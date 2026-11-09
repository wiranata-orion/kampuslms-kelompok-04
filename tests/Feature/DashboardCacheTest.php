<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\DashboardCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();
    }

    /** @return array<string, int> */
    private function stats(User $user): array
    {
        Sanctum::actingAs($user);

        return $this->getJson('/api/v1/dashboard')->assertOk()->json('data.stats');
    }

    public function test_tamu_tidak_bisa_membuka_dashboard(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_stats_admin_di_cache_lalu_dibersihkan_saat_user_dibuat(): void
    {
        $admin = User::factory()->admin()->create();
        $before = $this->stats($admin)['total_users'];

        // Tanpa event: cache tidak tahu ada perubahan, jadi angka lama masih dipakai.
        User::withoutEvents(fn () => User::factory()->create());
        $this->assertSame($before, $this->stats($admin)['total_users']);

        // Dengan event: observer membersihkan cache.
        User::factory()->create();
        $this->assertSame($before + 2, $this->stats($admin)['total_users']);
    }

    public function test_stats_dosen_diperbarui_saat_course_dan_assignment_dibuat(): void
    {
        $dosen = User::factory()->dosen()->create();
        $this->assertSame(0, $this->stats($dosen)['total_courses']);

        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $this->assertSame(1, $this->stats($dosen)['total_courses']);
        $this->assertSame(0, $this->stats($dosen)['total_assignments']);

        Assignment::factory()->create(['course_id' => $course->id]);
        $this->assertSame(1, $this->stats($dosen)['total_assignments']);
    }

    public function test_batas_perubahan_tanpa_event_baru_terlihat_setelah_forget_manual(): void
    {
        $dosen = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $this->assertSame(0, $this->stats($dosen)['total_assignments']);

        Assignment::withoutEvents(fn () => Assignment::factory()->create(['course_id' => $course->id]));
        $this->assertSame(0, $this->stats($dosen)['total_assignments']);

        DashboardCache::forgetLecturer($dosen->id);
        $this->assertSame(1, $this->stats($dosen)['total_assignments']);
    }

    public function test_stats_mahasiswa_diperbarui_saat_didaftarkan_dan_dikeluarkan_dari_course(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['status' => 'active']);

        $this->assertSame(0, $this->stats($student)['total_courses']);

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/courses/{$course->id}/enrollments", ['user_id' => $student->id])
            ->assertCreated();
        $this->assertSame(1, $this->stats($student)['total_courses']);

        Sanctum::actingAs($admin);
        $this->deleteJson("/api/v1/courses/{$course->id}/enrollments/{$student->id}")->assertOk();
        $this->assertSame(0, $this->stats($student)['total_courses']);
    }

    public function test_stats_mahasiswa_diperbarui_saat_submission_dibuat_dan_dihapus(): void
    {
        $student = User::factory()->create();
        $this->assertSame(0, $this->stats($student)['total_submissions']);

        $submission = Submission::factory()->create(['user_id' => $student->id]);
        $this->assertSame(1, $this->stats($student)['total_submissions']);

        $submission->delete();
        $this->assertSame(0, $this->stats($student)['total_submissions']);
    }

    public function test_cascade_hapus_assignment_membersihkan_cache_mahasiswa(): void
    {
        $student = User::factory()->create();
        $submission = Submission::factory()->create(['user_id' => $student->id]);
        $this->assertSame(1, $this->stats($student)['total_submissions']);

        // Submission dihapus oleh cascadeOnDelete di database (tanpa event Submission).
        $submission->assignment->delete();

        $this->assertSame(0, $this->stats($student)['total_submissions']);
    }

    public function test_cascade_hapus_course_membersihkan_cache_dosen_dan_mahasiswa(): void
    {
        $dosen = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $student = User::factory()->create();
        Submission::factory()->create(['assignment_id' => $assignment->id, 'user_id' => $student->id]);

        $this->assertSame(1, $this->stats($dosen)['total_courses']);
        $this->assertSame(1, $this->stats($student)['total_submissions']);

        $course->delete();

        $this->assertSame(0, $this->stats($dosen)['total_courses']);
        $this->assertSame(0, $this->stats($student)['total_submissions']);
    }

    public function test_rollback_transaksi_tidak_membersihkan_cache(): void
    {
        $admin = User::factory()->admin()->create();
        $this->stats($admin);
        $this->assertTrue(Cache::has(DashboardCache::adminKey()));

        try {
            DB::transaction(function () {
                User::factory()->create();

                throw new RuntimeException('paksa rollback');
            });
        } catch (RuntimeException) {
            // diharapkan
        }

        $this->assertTrue(Cache::has(DashboardCache::adminKey()));
    }

    public function test_blok_user_tidak_di_cache(): void
    {
        $dosen = User::factory()->dosen()->create();
        $this->stats($dosen);

        $dosen->update(['name' => 'Nama Baru']);

        Sanctum::actingAs($dosen->fresh());
        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Nama Baru');
    }
}
