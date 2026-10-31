<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_upload_stores_in_private_disk_with_random_name_and_validates_mimes_and_size(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status' => 'published',
            'due_at' => now()->addDays(2),
            'allow_late' => true,
        ]);

        // 1. Validasi: file wajib ada
        $this->actingAs($student, 'sanctum')
            ->postJson("/api/v1/assignments/{$assignment->id}/submissions", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        // 2. Validasi: ekstensi / mimes harus pdf/doc/docx (tolak file .php atau .txt)
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('malicious.php', 10, 'text/x-php'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        // 3. Validasi: file melebihi 5 MB (5120 KB) ditolak
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('huge.pdf', 5200, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        // 4. File valid (PDF, <= 5MB) berhasil disimpan di disk privat dengan nama fisik acak
        $response = $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('laporan_akhir.pdf', 2048, 'application/pdf'),
                'note' => 'Pengumpulan tugas pertama',
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.original_name', 'laporan_akhir.pdf')
            ->assertJsonPath('data.file_size', 2048 * 1024)
            ->assertJsonPath('data.file_size_formatted', '2 MB')
            ->assertJsonPath('data.is_late', false);

        $submission = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();
        $this->assertNotNull($submission);

        // Pastikan nama fisik acak (bukan laporan_akhir.pdf) dan disimpan di disk 'local'
        $this->assertNotEquals('laporan_akhir.pdf', basename($submission->file_path));
        Storage::disk('local')->assertExists($submission->file_path);
        // Pastikan TIDAK disimpan di public
        Storage::disk('public')->assertMissing($submission->file_path);
    }

    public function test_resubmission_uses_update_or_create_and_removes_old_file(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status' => 'published',
            'due_at' => now()->addDays(2),
            'allow_late' => true,
        ]);

        // Pengumpulan pertama
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('jawaban_v1.pdf', 100, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $submission1 = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();
        $oldFilePath = $submission1->file_path;
        Storage::disk('local')->assertExists($oldFilePath);

        // Pengumpulan kedua (revisi)
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('jawaban_v2.docx', 150, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.original_name', 'jawaban_v2.docx');

        // Pastikan baris di database tidak bertambah (unique composite assignment_id, user_id)
        $this->assertEquals(1, Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->count());

        $submission2 = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();
        $newFilePath = $submission2->file_path;

        // Berkas fisik lama harus sudah dihapus dari disk
        Storage::disk('local')->assertMissing($oldFilePath);
        Storage::disk('local')->assertExists($newFilePath);
    }

    public function test_server_calculates_is_late_correctly(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        // Tugas sudah lewat tenggat tetapi allow_late = true
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status' => 'published',
            'due_at' => now()->subHours(2),
            'allow_late' => true,
        ]);

        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('jawaban_terlambat.pdf', 50, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.is_late', true);
    }

    public function test_download_endpoint_authorizes_via_policy_and_serves_from_private_disk(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status' => 'published',
            'due_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('rahasia.pdf', 50, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $submissionId = $response->json('data.id');

        // Mahasiswa pemilik berhak mengunduh
        $this->actingAs($student, 'sanctum')
            ->get("/api/v1/submissions/{$submissionId}/download")
            ->assertOk()
            ->assertDownload('rahasia.pdf');

        // Dosen pengampu berhak mengunduh
        $this->actingAs($lecturer, 'sanctum')
            ->get("/api/v1/submissions/{$submissionId}/download")
            ->assertOk()
            ->assertDownload('rahasia.pdf');

        // Mahasiswa lain dilarang mengunduh (403 Forbidden)
        $this->actingAs($otherStudent, 'sanctum')
            ->get("/api/v1/submissions/{$submissionId}/download")
            ->assertForbidden();
    }

    public function test_deleting_submission_removes_physical_file_from_disk(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        $submission = new Submission;
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $student->id;
        $filePath = UploadedFile::fake()->create('hapus.pdf', 50, 'application/pdf')->store('submissions/'.$assignment->id, 'local');
        $submission->file_path = $filePath;
        $submission->original_name = 'hapus.pdf';
        $submission->file_size = 50 * 1024;
        $submission->submitted_at = now();
        $submission->save();

        Storage::disk('local')->assertExists($filePath);

        // Hapus via model Eloquent -> trigger event deleting
        $submission->delete();

        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_material_upload_stores_in_private_disk_and_has_human_readable_file_size(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);

        // Dosen mengunggah materi berkas (PPTX, maks 20 MB)
        $response = $this->actingAs($lecturer, 'sanctum')
            ->post("/api/v1/courses/{$course->id}/materials", [
                'title' => 'Pertemuan 09 File Upload',
                'description' => 'Materi slide kuliah',
                'type' => 'file',
                'file' => UploadedFile::fake()->create('minggu09.pptx', 4096, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.original_name', 'minggu09.pptx')
            ->assertJsonPath('data.file_size_formatted', '4 MB');

        $materialId = $response->json('data.id');
        $material = Material::find($materialId);
        $this->assertNotNull($material->file_path);
        Storage::disk('local')->assertExists($material->file_path);

        // Hapus materi -> berkas fisik terhapus
        $this->deleteJson("/api/v1/materials/{$materialId}")->assertOk();
        Storage::disk('local')->assertMissing($material->file_path);
    }

    public function test_student_cannot_edit_or_resubmit_after_submission_is_graded(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status' => 'published',
            'due_at' => now()->addDays(2),
            'allow_late' => true,
        ]);

        // 1. Mahasiswa mengumpulkan tugas
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('jawaban_awal.pdf', 100, 'application/pdf'),
                'note' => 'Versi pertama',
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $submission = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->firstOrFail();
        $originalFilePath = $submission->file_path;
        Storage::disk('local')->assertExists($originalFilePath);

        // 2. Dosen memberi nilai
        Grade::create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 88.5,
            'feedback' => 'Sudah bagus.',
            'graded_at' => now(),
        ]);

        // 3. Mahasiswa mencoba kumpulkan ulang / revisi -> DITOLAK (403 Forbidden)
        $this->actingAs($student, 'sanctum')
            ->post("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('revisi_setelah_nilai.pdf', 100, 'application/pdf'),
                'note' => 'Mencoba ganti berkas',
            ], ['Accept' => 'application/json'])
            ->assertForbidden();

        // 5. Pastikan berkas lama tidak terhapus atau tertimpa
        Storage::disk('local')->assertExists($originalFilePath);
        $this->assertEquals('jawaban_awal.pdf', $submission->fresh()->original_name);
    }
}

