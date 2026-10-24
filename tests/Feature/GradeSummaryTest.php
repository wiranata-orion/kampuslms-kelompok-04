<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_summary_endpoints_require_authentication(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'REKAP101');

        $this->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
        $this->getJson('/api/v1/my/grade-summary')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_lecturer_can_view_only_grade_rows_for_owned_course(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'REKAP201');
        $otherCourse = $this->createCourse($otherLecturer, 'REKAP202');
        $published = $this->createGrade(
            $this->createSubmission($this->createAssignment($course, $lecturer, 'Tugas dipublikasikan'), $student),
            $lecturer,
            88,
            'Bagus',
            true,
        );
        $this->createGrade(
            $this->createSubmission($this->createAssignment($course, $lecturer, 'Tugas belum dipublikasikan'), $student),
            $lecturer,
            60,
            'Perlu perbaikan',
        );
        $this->createGrade(
            $this->createSubmission($this->createAssignment($otherCourse, $otherLecturer, 'Tugas dosen lain'), $student),
            $otherLecturer,
            75,
            'Nilai mata kuliah lain',
            true,
        );
        $this->createSubmission($this->createAssignment($course, $lecturer, 'Tugas belum dinilai'), $student);

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary?page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.student.id', $student->id)
            ->assertJsonPath('data.0.course.id', $course->id)
            ->assertJsonPath('data.0.assignment.title', 'Tugas belum dipublikasikan')
            ->assertJsonPath('data.0.score', '60.00')
            ->assertJsonPath('data.0.feedback', 'Perlu perbaikan')
            ->assertJsonPath('data.0.is_published', false)
            ->assertJsonPath('data.1.id', $published->id)
            ->assertJsonPath('data.1.is_published', true);

        $this->actingAs($otherLecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertForbidden();
    }

    public function test_student_summary_contains_only_own_published_grades_for_enrolled_courses(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer, 'REKAP301');
        $unrelatedCourse = $this->createCourse($lecturer, 'REKAP302');
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);

        $visible = $this->createGrade(
            $this->createSubmission($this->createAssignment($course, $lecturer, 'Nilai terlihat'), $student),
            $lecturer,
            90,
            'Nilai milik sendiri',
            true,
        );
        $this->createGrade(
            $this->createSubmission($this->createAssignment($course, $lecturer, 'Nilai rahasia'), $student),
            $lecturer,
            40,
            'Feedback belum dipublikasikan',
        );
        $this->createGrade(
            $this->createSubmission($this->createAssignment($course, $lecturer, 'Nilai mahasiswa lain'), $otherStudent),
            $lecturer,
            55,
            'Tidak boleh terlihat',
            true,
        );
        $this->createGrade(
            $this->createSubmission($this->createAssignment($unrelatedCourse, $lecturer, 'Course belum diikuti'), $student),
            $lecturer,
            77,
            'Bukan course yang diikuti',
            true,
        );
        $this->createSubmission($this->createAssignment($course, $lecturer, 'Tugas belum dinilai'), $student);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/my/grade-summary?user_id='.$otherStudent->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $visible->id)
            ->assertJsonPath('data.0.student.id', $student->id)
            ->assertJsonPath('data.0.course.id', $course->id)
            ->assertJsonPath('data.0.assignment.title', 'Nilai terlihat')
            ->assertJsonPath('data.0.score', '90.00')
            ->assertJsonPath('data.0.feedback', 'Nilai milik sendiri')
            ->assertJsonPath('data.0.is_published', true)
            ->assertJsonCount(1, 'data')
            ->assertDontSee('Feedback belum dipublikasikan')
            ->assertDontSee('Tidak boleh terlihat')
            ->assertDontSee('Bukan course yang diikuti');

        $this->actingAs($otherStudent, 'sanctum')
            ->getJson('/api/v1/my/grade-summary')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.student.id', $otherStudent->id);

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/my/grade-summary')
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->getJson('/api/v1/my/grade-summary')
            ->assertForbidden();
    }

    public function test_grade_summaries_are_empty_when_course_has_no_graded_submissions(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'REKAP401');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $this->createSubmission($this->createAssignment($course, $lecturer, 'Belum dinilai'), $student);

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/my/grade-summary')
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            ]);

        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    }

    private function createCourse(User $lecturer, string $code): Course
    {
        $course = new Course;
        $course->code = $code;
        $course->name = 'Mata Kuliah '.$code;
        $course->description = 'Deskripsi';
        $course->sks = 3;
        $course->lecturer_id = $lecturer->id;
        $course->status = 'active';
        $course->save();

        return $course;
    }

    private function createAssignment(Course $course, User $lecturer, string $title): Assignment
    {
        $assignment = new Assignment;
        $assignment->course_id = $course->id;
        $assignment->created_by = $lecturer->id;
        $assignment->title = $title;
        $assignment->instructions = 'Instruksi';
        $assignment->due_at = now()->addDay();
        $assignment->max_score = 100;
        $assignment->allow_late = true;
        $assignment->status = 'published';
        $assignment->save();

        return $assignment;
    }

    private function createSubmission(Assignment $assignment, User $student): Submission
    {
        $submission = new Submission;
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $student->id;
        $submission->file_path = 'submissions/answer.pdf';
        $submission->original_name = 'answer.pdf';
        $submission->file_size = 1024;
        $submission->submitted_at = now();
        $submission->is_late = false;
        $submission->save();

        return $submission;
    }

    private function createGrade(
        Submission $submission,
        User $lecturer,
        int $score,
        string $feedback,
        bool $published = false,
    ): Grade {
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => now(),
        ]);

        if ($published) {
            $grade->published_at = now();
            $grade->save();
        }

        return $grade;
    }
}
