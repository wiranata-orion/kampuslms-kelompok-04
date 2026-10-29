<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DemoAccountSeeder;
use Database\Seeders\GradeSummaryDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeSummaryDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_summary_demo_seeder_is_idempotent_and_creates_expected_demo_data(): void
    {
        $this->seed(DemoAccountSeeder::class);

        $seeder = new GradeSummaryDemoSeeder;
        $seeder->run();
        $seeder->run();

        $lecturer = User::where('email', 'dosen@kampuslms.test')->firstOrFail();
        $student = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();
        $course = Course::where('code', 'REKAP-DEMO-01')->firstOrFail();

        $this->assertSame(1, User::where('email', 'dosen@kampuslms.test')->count());
        $this->assertSame(1, User::where('email', 'mahasiswa@kampuslms.test')->count());
        $this->assertSame($lecturer->id, $course->lecturer_id);
        $this->assertSame('active', $course->status);
        $this->assertTrue($course->students()->whereKey($student->id)->exists());
        $this->assertSame(1, Course::where('code', 'REKAP-DEMO-01')->count());
        $this->assertSame(4, Assignment::where('course_id', $course->id)->count());
        $this->assertSame(4, Submission::whereHas('assignment', fn ($query) => $query->where('course_id', $course->id))->count());
        $this->assertSame(3, Grade::whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))->count());
        $this->assertSame(2, Grade::whereNotNull('published_at')
            ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
            ->count());
        $this->assertSame(1, Grade::whereNull('published_at')
            ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
            ->count());
        $this->assertSame(1, Submission::whereHas('assignment', fn ($query) => $query->where('course_id', $course->id))
            ->whereDoesntHave('grade')
            ->count());
    }
}
