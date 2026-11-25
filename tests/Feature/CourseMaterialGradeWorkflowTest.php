<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradePublishedNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseMaterialGradeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_successfully_creates_course(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/courses', [
                'code' => 'CRUD101',
                'name' => 'Pemrograman Aman',
                'description' => 'Pengantar keamanan aplikasi.',
                'sks' => 3,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'CRUD101')
            ->assertJsonPath('data.lecturer.id', $lecturer->id);

        $this->assertDatabaseHas('courses', [
            'code' => 'CRUD101',
            'name' => 'Pemrograman Aman',
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
    }

    public function test_course_lecturer_successfully_uploads_material(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'MAT101');

        $response = $this->actingAs($lecturer, 'sanctum')
            ->post('/api/v1/courses/'.$course->id.'/materials', [
                'title' => 'Slide kuliah',
                'description' => 'Materi minggu pertama.',
                'type' => 'file',
                'file' => UploadedFile::fake()->create('slide.pdf', 2, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.original_name', 'slide.pdf')
            ->assertJsonPath('data.download_available', true);

        $this->assertDatabaseHas('materials', [
            'id' => $response->json('data.id'),
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'original_name' => 'slide.pdf',
        ]);
        Storage::disk('local')->assertExists($response->json('data.file_path'));
    }

    public function test_other_lecturer_is_forbidden_from_uploading_material(): void
    {
        Storage::fake('local');
        $courseLecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($courseLecturer, 'MAT102');

        $this->actingAs($otherLecturer, 'sanctum')
            ->post('/api/v1/courses/'.$course->id.'/materials', [
                'title' => 'Unggahan tanpa izin',
                'description' => 'Tidak boleh tersimpan.',
                'type' => 'file',
                'file' => UploadedFile::fake()->create('forbidden.pdf', 2, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertForbidden();

        $this->assertDatabaseMissing('materials', [
            'course_id' => $course->id,
            'title' => 'Unggahan tanpa izin',
        ]);
        $this->assertSame([], Storage::disk('local')->allFiles("materials/{$course->id}"));
    }

    public function test_course_lecturer_successfully_grades_submission(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'GRD101');
        $submission = $this->createSubmission($this->createAssignment($course, $lecturer), $student);

        $response = $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/submissions/'.$submission->id.'/grade', [
                'score' => 88,
                'feedback' => 'Bagus.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.score', '88.00');

        $this->assertDatabaseHas('grades', [
            'id' => $response->json('data.id'),
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 88,
        ]);
    }

    public function test_other_lecturer_is_forbidden_from_grading_submission(): void
    {
        $courseLecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($courseLecturer, 'GRD102');
        $submission = $this->createSubmission($this->createAssignment($course, $courseLecturer), $student);

        $this->actingAs($otherLecturer, 'sanctum')
            ->putJson('/api/v1/submissions/'.$submission->id.'/grade', [
                'score' => 88,
                'feedback' => 'Tidak boleh menilai.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('grades', ['submission_id' => $submission->id]);
    }

    public function test_published_assignment_notifies_enrolled_students(): void
    {
        Notification::fake();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $outsider = User::factory()->create();
        $course = $this->createCourse($lecturer, 'NTF101');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $response = $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/assignments', [
                'course_id' => $course->id,
                'title' => 'Tugas yang dipublikasikan',
                'instructions' => 'Kerjakan soal.',
                'due_at' => now()->addWeek()->toDateTimeString(),
                'max_score' => 100,
                'allow_late' => true,
                'status' => 'published',
            ])
            ->assertCreated();
        $assignmentId = $response->json('data.id');

        Notification::assertSentTo(
            $student,
            NewAssignmentNotification::class,
            fn (NewAssignmentNotification $notification): bool => $notification->assignment->id === $assignmentId,
        );
        Notification::assertNotSentTo($outsider, NewAssignmentNotification::class);
    }

    public function test_published_grade_notifies_only_its_submission_student(): void
    {
        Notification::fake();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer, 'NTF102');
        $submission = $this->createSubmission($this->createAssignment($course, $lecturer), $student);
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'feedback' => 'Sudah dinilai.',
            'graded_at' => now(),
        ]);

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/grades/'.$grade->id.'/publish')
            ->assertOk()
            ->assertJsonPath('data.published_at', fn ($value) => $value !== null);

        Notification::assertSentTo(
            $student,
            GradePublishedNotification::class,
            fn (GradePublishedNotification $notification): bool => $notification->grade->id === $grade->id,
        );
        Notification::assertNotSentTo($otherStudent, GradePublishedNotification::class);
    }

    private function createCourse(User $lecturer, string $code): Course
    {
        return Course::factory()->create([
            'code' => $code,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
    }

    private function createAssignment(Course $course, User $lecturer): Assignment
    {
        $assignment = new Assignment;
        $assignment->course_id = $course->id;
        $assignment->created_by = $lecturer->id;
        $assignment->title = 'Tugas '.$course->code;
        $assignment->instructions = 'Kerjakan tugas.';
        $assignment->due_at = now()->addWeek();
        $assignment->max_score = 100;
        $assignment->allow_late = true;
        $assignment->status = 'published';
        $assignment->save();

        return $assignment;
    }

    private function createSubmission(Assignment $assignment, User $student): Submission
    {
        return Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/answer.pdf',
            'original_name' => 'answer.pdf',
            'file_size' => 1024,
            'submitted_at' => now(),
            'is_late' => false,
        ]);
    }
}
