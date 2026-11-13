<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\CourseScheduleService;
use Tests\TestCase;

class CourseScheduleAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_schedule_generates_one_session_per_matching_date(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'SCH101');

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$course->id.'/schedules', $this->scheduleData())
            ->assertCreated()
            ->assertJsonPath('data.weekday', 1)
            ->assertJsonPath('data.room', 'A-101');

        $this->assertDatabaseCount('course_schedules', 1);
        $this->assertDatabaseCount('course_sessions', 3);
        $this->assertDatabaseHas('course_sessions', [
            'course_id' => $course->id,
            'session_date' => '2026-11-16 00:00:00',
            'starts_at' => '2026-11-16 01:00:00',
            'ends_at' => '2026-11-16 02:40:00',
        ]);

        $scheduleId = $this->getJson('/api/v1/courses/'.$course->id.'/schedules')->json('data.0.id');
        $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/course-schedules/'.$scheduleId, $this->scheduleData())
            ->assertOk();
        $this->assertDatabaseCount('course_sessions', 3);
    }

    public function test_conflicting_schedule_returns_reason_and_alternative_slots(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $firstCourse = $this->createCourse($lecturer, 'SCH201');
        $secondCourse = $this->createCourse($lecturer, 'SCH202');
        $firstCourse->schedules()->create($this->scheduleAttributes());

        $response = $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$secondCourse->id.'/schedules', $this->scheduleData([
                'room' => 'a-101',
                'starts_at' => '09:30',
                'ends_at' => '10:30',
            ]))
            ->assertStatus(409)
            ->assertJsonPath('conflicts.0.course_code', 'SCH201')
            ->assertJsonPath('conflicts.0.reasons.0', 'dosen pengampu yang sama')
            ->assertJsonPath('conflicts.0.reasons.1', 'ruang yang sama')
            ->assertJsonStructure(['message', 'conflicts', 'suggestions']);

        $this->assertNotEmpty($response->json('suggestions'));
        $this->assertSame('Senin', $response->json('suggestions.0.weekday_name'));
        $this->assertDatabaseCount('course_schedules', 1);
    }

    public function test_room_conflict_is_reported_when_lecturers_differ(): void
    {
        $firstLecturer = User::factory()->dosen()->create();
        $secondLecturer = User::factory()->dosen()->create();
        $firstCourse = $this->createCourse($firstLecturer, 'SCH211');
        $secondCourse = $this->createCourse($secondLecturer, 'SCH212');
        $firstCourse->schedules()->create($this->scheduleAttributes());

        $this->actingAs($secondLecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$secondCourse->id.'/schedules', $this->scheduleData())
            ->assertStatus(409)
            ->assertJsonPath('conflicts.0.reasons', ['ruang yang sama']);
    }

    public function test_same_session_code_is_reused_and_student_can_attend_only_once(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 09:10:00', 'Asia/Makassar'));
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'SCH301');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $this->createSchedule($course);
        $session = $course->sessions()->whereDate('session_date', '2026-11-16')->firstOrFail();

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/sessions/'.$session->id.'/attendance-code')
            ->assertOk();
        $firstCode = $this->getJson('/api/v1/sessions/'.$session->id.'/attendance-code')->json('data.code');
        $secondCode = $this->getJson('/api/v1/sessions/'.$session->id.'/attendance-code')->json('data.code');
        $this->assertSame($firstCode, $secondCode);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/v1/sessions/'.$session->id.'/attendance', ['code' => $firstCode, 'source' => 'qr'])
            ->assertCreated()
            ->assertJsonPath('data.message', 'Presensi berhasil dicatat.');

        $this->postJson('/api/v1/sessions/'.$session->id.'/attendance', ['code' => $firstCode])
            ->assertStatus(409);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertDatabaseHas('attendance_records', [
            'course_session_id' => $session->id,
            'user_id' => $student->id,
            'source' => 'qr',
        ]);
    }

    public function test_invalid_code_and_non_enrolled_student_cannot_record_attendance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 09:10:00', 'Asia/Makassar'));
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $outsider = User::factory()->create();
        $course = $this->createCourse($lecturer, 'SCH401');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $this->createSchedule($course);
        $session = $course->sessions()->whereDate('session_date', '2026-11-16')->firstOrFail();
        $code = app(\App\Services\AttendanceCodeService::class)->forSession($session);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/v1/sessions/'.$session->id.'/attendance', ['code' => 'INVALID'])
            ->assertUnprocessable();

        $this->actingAs($outsider, 'sanctum')
            ->postJson('/api/v1/sessions/'.$session->id.'/attendance', ['code' => $code])
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_attendance_code_is_unavailable_outside_the_scheduled_session(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 11:01:00', 'Asia/Makassar'));
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'SCH501');
        $this->createSchedule($course);
        $session = $course->sessions()->whereDate('session_date', '2026-11-16')->firstOrFail();

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/sessions/'.$session->id.'/attendance-code')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Kode presensi hanya tersedia selama sesi berlangsung.');
    }

    private function createCourse(User $lecturer, string $code): Course
    {
        $course = new Course([
            'code' => $code,
            'name' => 'Course '.$code,
            'description' => '',
            'sks' => 3,
        ]);
        $course->lecturer_id = $lecturer->id;
        $course->status = 'active';
        $course->save();

        return $course;
    }

    private function createSchedule(Course $course): CourseSchedule
    {
        $schedule = $course->schedules()->create($this->scheduleAttributes());
        app(CourseScheduleService::class)->syncSessions($schedule);

        return $schedule;
    }

    private function scheduleData(array $overrides = []): array
    {
        return array_merge($this->scheduleAttributes(), $overrides);
    }

    private function scheduleAttributes(): array
    {
        return [
            'weekday' => 1,
            'starts_at' => '09:00',
            'ends_at' => '10:40',
            'room' => 'A-101',
            'effective_from' => '2026-11-16',
            'effective_until' => '2026-11-30',
        ];
    }
}
