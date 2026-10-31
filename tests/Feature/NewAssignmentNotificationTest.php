<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Listeners\SendNewAssignmentNotifications;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class NewAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_store_dispatches_assignment_published_event(): void
    {
        Event::fake([AssignmentPublished::class]);

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);

        $response = $this->actingAs($lecturer, 'sanctum')->postJson('/api/v1/assignments', [
            'course_id' => $course->id,
            'title' => 'Tugas Struktur Data Pertemuan 1',
            'instructions' => 'Kerjakan modul latihan 1-3.',
            'due_at' => now()->addDays(7)->toIso8601String(),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ]);

        $response->assertCreated();

        Event::assertDispatched(AssignmentPublished::class, function (AssignmentPublished $event) {
            return $event->assignment->title === 'Tugas Struktur Data Pertemuan 1';
        });
    }

    public function test_send_new_assignment_notifications_listener_sends_notification_to_enrolled_students(): void
    {
        Notification::fake();

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);

        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        $outsider = User::factory()->create();

        $course->students()->attach([$student1->id, $student2->id], ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Algoritma',
            'due_at' => now()->addDays(5),
            'status' => 'published',
        ]);

        $listener = new SendNewAssignmentNotifications();
        $listener->handle(new AssignmentPublished($assignment));

        Notification::assertSentTo(
            [$student1, $student2],
            NewAssignmentNotification::class,
            function (NewAssignmentNotification $notification) use ($assignment) {
                return $notification->assignment->id === $assignment->id;
            }
        );

        Notification::assertNotSentTo([$outsider], NewAssignmentNotification::class);
    }

    public function test_new_assignment_notification_payload_structure_and_excludes_grade_info(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'name' => 'Pemrograman Web']);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Vue Component',
            'due_at' => now()->addDays(3),
        ]);
        $student = User::factory()->create();

        $notification = new NewAssignmentNotification($assignment);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['database'], $notification->via($student));

        $payload = $notification->toArray($student);

        // Memastikan payload memuat ID, judul, nama mata kuliah, due_date, pesan, dan URL
        $this->assertSame($assignment->id, $payload['assignment_id']);
        $this->assertSame('Tugas Vue Component', $payload['title']);
        $this->assertSame('Pemrograman Web', $payload['course_name']);
        $this->assertNotNull($payload['due_date']);
        $this->assertStringContainsString('Tugas Vue Component', $payload['message']);
        $this->assertStringContainsString((string) $assignment->id, $payload['url']);

        // Sesuai modul poin 8: payload tidak boleh memuat nilai atau feedback
        $this->assertArrayNotHasKey('grade', $payload);
        $this->assertArrayNotHasKey('nilai', $payload);
        $this->assertArrayNotHasKey('score', $payload);
        $this->assertArrayNotHasKey('feedback', $payload);
    }

    public function test_database_notification_is_stored_and_readable_by_student(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'name' => 'Jaringan Komputer']);
        $student = User::factory()->create();
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Subnetting IPv4',
            'due_at' => now()->addDays(4),
        ]);

        $listener = new SendNewAssignmentNotifications();
        $listener->handle(new AssignmentPublished($assignment));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $student->id,
            'notifiable_type' => User::class,
            'type' => NewAssignmentNotification::class,
        ]);

        $this->assertSame(1, $student->unreadNotifications()->count());

        $notificationData = $student->notifications()->first()->data;
        $this->assertSame($assignment->id, $notificationData['assignment_id']);
        $this->assertSame('Subnetting IPv4', $notificationData['title']);
    }

    public function test_listener_queue_configuration_and_failed_method(): void
    {
        $listener = new SendNewAssignmentNotifications();

        $this->assertInstanceOf(ShouldQueue::class, $listener);
        $this->assertSame(3, $listener->tries);
        $this->assertSame([10, 30, 60], $listener->backoff);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return str_contains($message, 'SendNewAssignmentNotifications')
                    && isset($context['message'])
                    && $context['message'] === 'Network timeout';
            });

        $assignment = new Assignment();
        $assignment->id = 123;
        $event = new AssignmentPublished($assignment);

        $listener->failed($event, new RuntimeException('Network timeout'));
    }
}
