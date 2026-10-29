<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradePublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSummaryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_grade_notifies_its_student_once_and_draft_grades_do_not_notify(): void
    {
        [$lecturer, $student, $course, $assignment, $submission] = $this->createGradedSubmission();
        $outsider = User::factory()->create();
        $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/submissions/'.$submission->id.'/grade', ['score' => 88, 'feedback' => 'Bagus'])
            ->assertCreated();

        $this->assertSame(0, $student->notifications()->count());
        $grade = Grade::query()->where('submission_id', $submission->id)->firstOrFail();

        $this->postJson('/api/v1/grades/'.$grade->id.'/publish')->assertOk();
        $this->postJson('/api/v1/grades/'.$grade->id.'/publish')->assertOk();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(0, $outsider->notifications()->count());
        $notification = $student->notifications()->firstOrFail();
        $this->assertSame(GradePublishedNotification::class, $notification->type);
        $this->assertSame('Nilai tugas dipublikasikan', $notification->data['title']);
        $this->assertStringContainsString($assignment->title, $notification->data['message']);
        $this->assertStringContainsString($course->name, $notification->data['message']);
        $this->assertSame('/submissions/'.$submission->id.'/grade', parse_url($notification->data['url'], PHP_URL_PATH));
        $this->assertArrayNotHasKey('score', $notification->data);
        $this->assertArrayNotHasKey('feedback', $notification->data);
        $this->assertNull($notification->read_at);
    }

    public function test_grade_summaries_are_authorized_and_student_sees_only_own_published_grades(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);

        $ownPublished = $this->createGradeFor($course, $lecturer, $student, true);
        $this->createGradeFor($course, $lecturer, $student, false);
        $this->createGradeFor($course, $lecturer, $otherStudent, true);

        $this->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertUnauthorized();

        $this->actingAs($otherLecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/grade-summary')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);

        $studentSummary = $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/my/grade-summary')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
        $this->assertSame([$ownPublished->id], collect($studentSummary->json('data'))->pluck('id')->all());
    }

    private function createGradedSubmission(): array
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        return [$lecturer, $student, $course, $assignment, $submission];
    }

    private function createGradeFor(Course $course, User $lecturer, User $student, bool $published): Grade
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $grade = Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
        ]);

        if ($published) {
            $grade->forceFill(['published_at' => now()])->save();
        }

        return $grade;
    }
}
