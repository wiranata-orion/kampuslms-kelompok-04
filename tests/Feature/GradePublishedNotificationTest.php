<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradePublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class GradePublishedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_grade_publication_notifies_only_the_submission_student_once(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer);
        $assignment = $this->createAssignment($course, $lecturer);
        $submission = $this->createSubmission($assignment, $student);
        $grade = $this->createGrade($submission, $lecturer);

        $this->assertDatabaseCount('notifications', 0);

        $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/grades/'.$grade->id, [
                'score' => 85,
                'feedback' => 'Feedback sebelum dipublikasikan',
            ])
            ->assertOk();
        $this->assertDatabaseCount('notifications', 0);

        $this->postJson('/api/v1/grades/'.$grade->id.'/publish')
            ->assertOk()
            ->assertJsonPath('data.published_at', fn ($value) => $value !== null);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'type' => GradePublishedNotification::class,
            'notifiable_type' => $student->getMorphClass(),
            'notifiable_id' => $student->id,
        ]);
        $this->assertSame(0, $otherStudent->notifications()->count());

        $notification = $student->notifications()->firstOrFail();
        $this->assertSame($grade->id, $notification->data['grade_id']);
        $this->assertSame($assignment->id, $notification->data['assignment_id']);
        $this->assertSame($assignment->title, $notification->data['assignment_title']);
        $this->assertSame($course->id, $notification->data['course_id']);
        $this->assertSame($course->name, $notification->data['course_name']);
        $this->assertSame(url('/submissions/'.$submission->id.'/grade'), $notification->data['url']);
        $this->assertSame('Nilai tugas dipublikasikan', $notification->data['title']);
        $this->assertSame($notification->data['title'], $notification->data['judul']);
        $this->assertStringContainsString('telah dipublikasikan', $notification->data['message']);
        $this->assertArrayNotHasKey('score', $notification->data);
        $this->assertArrayNotHasKey('feedback', $notification->data);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/notifications?per_page=5')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $notification->id)
            ->assertJsonPath('data.0.data.title', 'Nilai tugas dipublikasikan')
            ->assertJsonPath('data.0.data.message', $notification->data['message'])
            ->assertJsonPath('data.0.read_at', null);
        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);

        $this->postJson('/api/v1/notifications/'.$notification->id.'/read')
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 0);

        $publishedAt = $grade->fresh()->published_at;
        $this->actingAs($lecturer, 'sanctum');
        $this->postJson('/api/v1/grades/'.$grade->id.'/publish')->assertOk();
        $this->assertSame(1, $student->notifications()->count());
        $this->assertEquals($publishedAt, $grade->fresh()->published_at);

        $this->putJson('/api/v1/grades/'.$grade->id, [
            'score' => 90,
            'feedback' => 'Nilai diperbarui',
        ])->assertOk();
        $this->postJson('/api/v1/grades/'.$grade->id.'/publish')->assertOk();
        $this->assertSame(1, $student->notifications()->count());
    }

    public function test_notification_endpoints_are_authenticated_and_scoped_to_owner(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $ownNotificationId = (string) Str::uuid();
        $otherNotificationId = (string) Str::uuid();
        $this->insertNotification($ownNotificationId, $student);
        $this->insertNotification($otherNotificationId, $otherStudent);

        $this->getJson('/api/v1/notifications')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
        $this->postJson('/api/v1/notifications/'.$ownNotificationId.'/read')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $ownNotificationId);

        $this->postJson('/api/v1/notifications/'.$otherNotificationId.'/read')
            ->assertForbidden();

        $this->postJson('/api/v1/notifications/'.$ownNotificationId.'/read')
            ->assertOk()
            ->assertJsonPath('data.id', $ownNotificationId)
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
        $readAt = DB::table('notifications')->where('id', $ownNotificationId)->value('read_at');

        $this->postJson('/api/v1/notifications/'.$ownNotificationId.'/read')
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
        $this->assertSame($readAt, DB::table('notifications')->where('id', $ownNotificationId)->value('read_at'));

        $this->postJson('/api/v1/notifications/'.Str::uuid().'/read')
            ->assertNotFound();
    }

    public function test_notification_endpoints_check_the_notifiable_type_as_well_as_the_user_id(): void
    {
        $student = User::factory()->create();
        $notificationId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\Course',
            'notifiable_id' => $student->id,
            'data' => json_encode(['message' => 'Notifikasi bukan milik akun pengguna'], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/notifications/'.$notificationId)
            ->assertForbidden();
        $this->postJson('/api/v1/notifications/'.$notificationId.'/read')
            ->assertForbidden();
        $this->assertNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }

    private function createCourse(User $lecturer): Course
    {
        $course = new Course;
        $course->code = 'NOTIF101';
        $course->name = 'Pemrograman Web';
        $course->description = 'Deskripsi';
        $course->sks = 3;
        $course->lecturer_id = $lecturer->id;
        $course->status = 'active';
        $course->save();

        return $course;
    }

    private function createAssignment(Course $course, User $lecturer): Assignment
    {
        $assignment = new Assignment;
        $assignment->course_id = $course->id;
        $assignment->created_by = $lecturer->id;
        $assignment->title = 'Tugas API';
        $assignment->instructions = 'Kerjakan';
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

    private function createGrade(Submission $submission, User $lecturer): Grade
    {
        return Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'feedback' => 'Belum dipublikasikan',
            'graded_at' => now(),
        ]);
    }

    private function insertNotification(string $id, User $user): void
    {
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'Pengumuman'], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
