<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $this->get('/api/v1/courses')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_invalid_login_uses_the_api_validation_contract(): void
    {
        $this->post('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
    }

    public function test_forbidden_assignment_request_returns_the_contract_message(): void
    {
        $student = new User(['role' => 'mahasiswa']);

        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments', [])
            ->assertStatus(403)
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);
    }

    public function test_paginated_collection_has_only_contract_meta_and_hides_password(): void
    {
        $user = new User([
            'name' => 'Dosen API',
            'email' => 'dosen@example.test',
            'password' => 'secret-hash',
            'role' => 'dosen',
        ]);
        $paginator = new LengthAwarePaginator(collect([$user]), 1, 15, 1);
        $response = ApiResponse::collection(
            $paginator,
            UserResource::class,
            Request::create('/api/v1/users?page=1', 'GET'),
        );
        $body = $response->getData(true);

        $this->assertSame(['data', 'meta'], array_keys($body));
        $this->assertSame(['current_page' => 1, 'last_page' => 1, 'total' => 1], $body['meta']);
        $this->assertArrayNotHasKey('password', $body['data'][0]);
    }

    public function test_sanctum_login_me_and_logout_work_with_explicit_user_resource(): void
    {
        $user = User::factory()->admin()->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-client',
        ])->assertOk()
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonMissingPath('data.user.password');

        $token = $login->json('data.token');
        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', 'admin');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_course_collections_are_scoped_and_include_counts_and_lecturer(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $enrolledCourse = $this->createCourse($lecturer, 'API101');
        $otherCourse = $this->createCourse(User::factory()->dosen()->create(), 'API102');
        $enrolledCourse->students()->attach($student->id, ['enrolled_at' => now()]);

        $response = $this->actingAs($student, 'sanctum')->getJson('/api/v1/courses')->assertOk();
        $body = $response->json();

        $this->assertSame(['data', 'meta'], array_keys($body));
        $this->assertSame(['current_page' => 1, 'last_page' => 1, 'total' => 1], $body['meta']);
        $this->assertSame('API101', $body['data'][0]['code']);
        $this->assertSame($lecturer->id, $body['data'][0]['lecturer_id']);
        $this->assertSame($lecturer->id, $body['data'][0]['lecturer']['id']);
        $this->assertSame(['materials' => 0, 'assignments' => 0], $body['data'][0]['counts']);

        $this->getJson('/api/v1/my/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'API101');
        $this->getJson('/api/v1/courses?scope=all&status=active')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/courses/'.$otherCourse->id)
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);
    }

    public function test_material_and_assignment_collections_use_resources_and_hide_drafts(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API201');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $lecturer->id;
        $material->title = 'Materi API';
        $material->description = 'Deskripsi';
        $material->type = 'file';
        $material->file_path = 'private/materials/internal.pdf';
        $material->original_name = 'materi.pdf';
        $material->file_size = 123;
        $material->mime_type = 'application/pdf';
        $material->save();

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/materials')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Materi API')
            ->assertJsonPath('data.0.uploader.id', $lecturer->id)
            ->assertJsonMissingPath('data.0.file_path');

        $published = $this->createAssignment($course, $lecturer, 'published');
        $this->createAssignment($course, $lecturer, 'draft');

        $this->getJson('/api/v1/courses/'.$course->id.'/assignments?status=published')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.status', 'published');
    }

    public function test_material_file_download_is_authenticated_and_course_scoped(): void
    {
        Storage::fake('public');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API250');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $path = UploadedFile::fake()->create('slide.pdf', 10, 'application/pdf')
            ->store('materials', 'public');

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $lecturer->id;
        $material->title = 'Slide kuliah';
        $material->description = '';
        $material->type = 'file';
        $material->file_path = $path;
        $material->original_name = 'slide.pdf';
        $material->file_size = 10240;
        $material->mime_type = 'application/pdf';
        $material->save();

        $this->actingAs($student, 'sanctum')
            ->get('/api/v1/materials/'.$material->id.'/download')
            ->assertOk()
            ->assertDownload('slide.pdf');

        $outsider = User::factory()->create();
        $this->actingAs($outsider, 'sanctum')
            ->getJson('/api/v1/materials/'.$material->id.'/download')
            ->assertForbidden();
    }

    public function test_admin_user_course_and_enrollment_crud_are_available_through_api(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();

        $studentResponse = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/users', [
            'name' => 'Mahasiswa API',
            'email' => 'mahasiswa-api@example.test',
            'password' => 'password',
            'role' => 'mahasiswa',
            'nim_nip' => 'MHS-API-1',
        ])->assertCreated()->assertJsonPath('data.role', 'mahasiswa');
        $studentId = $studentResponse->json('data.id');

        $this->putJson('/api/v1/users/'.$studentId, [
            'name' => 'Mahasiswa API Diubah',
            'email' => 'mahasiswa-api@example.test',
            'role' => 'mahasiswa',
            'nim_nip' => 'MHS-API-1',
        ])->assertOk()->assertJsonPath('data.name', 'Mahasiswa API Diubah');

        $courseResponse = $this->postJson('/api/v1/courses', [
            'code' => 'CRUD101',
            'name' => 'Mata Kuliah CRUD',
            'description' => 'Kelas API',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.code', 'CRUD101');
        $courseId = $courseResponse->json('data.id');

        $this->putJson('/api/v1/courses/'.$courseId, [
            'code' => 'CRUD101',
            'name' => 'Mata Kuliah CRUD Diubah',
            'description' => 'Kelas API',
            'sks' => 3,
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ])->assertOk()->assertJsonPath('data.name', 'Mata Kuliah CRUD Diubah');

        $this->getJson('/api/v1/courses/'.$courseId.'/enrollments/candidates')
            ->assertOk()
            ->assertJsonPath('data.candidates.0.id', $studentId);

        $this->postJson('/api/v1/courses/'.$courseId.'/enrollments', ['user_id' => $studentId])
            ->assertCreated();
        $this->getJson('/api/v1/courses/'.$courseId.'/students')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $studentId);
        $this->postJson('/api/v1/courses/'.$courseId.'/enrollments', ['user_id' => $studentId])
            ->assertStatus(409);
        $this->deleteJson('/api/v1/courses/'.$courseId.'/enrollments/'.$studentId)
            ->assertOk();

        $this->deleteJson('/api/v1/courses/'.$courseId)->assertOk();
        $this->deleteJson('/api/v1/users/'.$studentId)->assertOk();
    }

    public function test_lecturer_can_create_read_update_and_delete_link_materials_through_api(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'MAT401');
        $this->actingAs($lecturer, 'sanctum');

        $materialResponse = $this->postJson('/api/v1/courses/'.$course->id.'/materials', [
            'title' => 'Referensi API',
            'description' => 'Tautan referensi',
            'type' => 'link',
            'external_url' => 'https://example.test/reference',
        ])->assertCreated()->assertJsonPath('data.title', 'Referensi API');
        $materialId = $materialResponse->json('data.id');

        $this->getJson('/api/v1/materials/'.$materialId)
            ->assertOk()
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.external_url', 'https://example.test/reference');

        $this->putJson('/api/v1/materials/'.$materialId, [
            'title' => 'Referensi API Diubah',
            'description' => 'Tautan diperbarui',
            'type' => 'link',
            'external_url' => 'https://example.test/updated',
        ])->assertOk()->assertJsonPath('data.title', 'Referensi API Diubah')
            ->assertJsonPath('data.external_url', 'https://example.test/updated');

        $this->deleteJson('/api/v1/materials/'.$materialId)->assertOk();
        $this->assertDatabaseMissing('materials', ['id' => $materialId]);
    }

    public function test_assignment_submission_and_grade_endpoints_use_contract_statuses(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API301');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignmentResponse = $this->actingAs($lecturer, 'sanctum')->postJson('/api/v1/assignments', [
            'course_id' => $course->id,
            'title' => 'Tugas API',
            'instructions' => 'Kerjakan',
            'due_at' => now()->addDay()->toIso8601String(),
            'status' => 'published',
        ])->assertCreated();
        $assignmentId = $assignmentResponse->json('data.id');

        $submission = $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments/'.$assignmentId.'/submissions', [
                'file' => UploadedFile::fake()->create('jawaban.pdf', 10, 'application/pdf'),
                'note' => 'Jawaban',
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonMissingPath('data.file_path');
        $submissionId = $submission->json('data.id');

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/assignments/'.$assignmentId.'/submissions')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.student.id', $student->id);

        $grade = $this->putJson('/api/v1/submissions/'.$submissionId.'/grade', [
            'score' => 80,
            'feedback' => 'Baik',
        ])->assertCreated()->assertJsonPath('data.score', '80.00');
        $gradeId = $grade->json('data.id');

        $this->getJson('/api/v1/grades/'.$gradeId)
            ->assertOk()
            ->assertJsonPath('data.id', $submissionId)
            ->assertJsonPath('data.grade.id', $gradeId)
            ->assertJsonPath('data.assignment.course.code', 'API301');

        $this->putJson('/api/v1/submissions/'.$submissionId.'/grade', [
            'score' => 90,
            'feedback' => 'Revisi nilai',
        ])->assertOk()->assertJsonPath('data.score', '90.00');

        $this->assertDatabaseCount('grades', 1);

        $this->patchJson('/api/v1/assignments/'.$assignmentId, ['title' => 'Tugas Revisi'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Tugas Revisi');
        $this->deleteJson('/api/v1/assignments/'.$assignmentId)->assertNoContent();
    }

    public function test_notifications_are_limited_to_the_authenticated_user_and_can_be_read(): void
    {
        $user = User::factory()->create();
        $notificationId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'Tugas baru']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.data.message', 'Tugas baru');

        $this->postJson('/api/v1/notifications/'.$notificationId.'/read')
            ->assertOk()
            ->assertJsonPath('data.id', $notificationId)
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);

        $secondNotificationId = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $secondNotificationId,
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'Pengumuman']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->postJson('/api/v1/notifications/read-all')->assertOk();
        $this->assertNotNull(DB::table('notifications')->where('id', $secondNotificationId)->value('read_at'));
    }

    private function createCourse(User $lecturer, string $code): Course
    {
        $course = new Course();
        $course->code = $code;
        $course->name = 'Mata Kuliah API';
        $course->description = 'Deskripsi';
        $course->sks = 3;
        $course->lecturer_id = $lecturer->id;
        $course->status = 'active';
        $course->save();

        return $course;
    }

    private function createAssignment(Course $course, User $lecturer, string $status): Assignment
    {
        $assignment = new Assignment();
        $assignment->course_id = $course->id;
        $assignment->created_by = $lecturer->id;
        $assignment->title = 'Tugas '.$status;
        $assignment->instructions = 'Instruksi';
        $assignment->due_at = now()->addDay();
        $assignment->max_score = 100;
        $assignment->allow_late = true;
        $assignment->status = $status;
        $assignment->save();

        return $assignment;
    }
}