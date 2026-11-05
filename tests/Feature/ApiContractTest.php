<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Policies\SubmissionPolicy;
use App\Support\ApiResponse;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function test_direct_password_reset_can_find_a_user_by_nim_or_email(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.test',
            'nim_nip' => 'NIM-RESET-1',
        ]);

        $this->postJson('/api/v1/auth/check-user', ['identifier' => $user->nim_nip])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson('/api/v1/auth/check-user', ['identifier' => $user->email])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson('/api/v1/auth/check-user', ['identifier' => 'missing@example.test'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_demo_admin_cannot_use_password_reset_but_other_demo_accounts_can(): void
    {
        $this->seed(DemoAccountSeeder::class);

        $this->postJson('/api/v1/auth/check-user', ['identifier' => 'admin@kampuslms.test'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Akun admin tidak dapat mengganti kata sandi melalui fitur ini.');

        foreach ([
            'dosen@kampuslms.test',
            'mahasiswa@kampuslms.test',
            '198801012022011001',
            '10241001',
        ] as $identifier) {
            $this->postJson('/api/v1/auth/check-user', ['identifier' => $identifier])
                ->assertOk()
                ->assertJsonPath('success', true);
        }
    }

    public function test_admin_password_cannot_be_changed_through_reset_endpoint(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-reset@example.test',
            'password' => 'current-password',
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'identifier' => $admin->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Akun admin tidak dapat mengganti kata sandi melalui fitur ini.');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_database_seeder_runs_with_lazy_loading_prevention_enabled(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 34);
        $this->assertGreaterThan(0, Grade::query()->count());
    }

    public function test_direct_password_reset_updates_the_password_for_a_matching_nim(): void
    {
        $user = User::factory()->create(['nim_nip' => 'NIM-RESET-2']);
        $user->createToken('existing-session');

        $this->postJson('/api/v1/auth/reset-password', [
            'identifier' => $user->nim_nip,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
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
        $paginator = new LengthAwarePaginator(collect([$user]), 1, 20, 1);
        $response = ApiResponse::collection(
            $paginator,
            UserResource::class,
            Request::create('/api/v1/users?page=1', 'GET'),
        );
        $body = $response->getData(true);

        $this->assertSame(['data', 'meta'], array_keys($body));
        $this->assertSame(['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 1], $body['meta']);
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
        $this->assertSame(['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 1], $body['meta']);
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
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissing(['id' => $otherCourse->id]);

        $this->getJson('/api/v1/courses/'.$otherCourse->id)
            ->assertForbidden()
            ->assertExactJson(['message' => 'Anda tidak memiliki akses ke sumber daya ini.']);
    }

    public function test_my_courses_returns_twenty_rows_and_pagination_metadata_per_page(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();

        for ($index = 1; $index <= 21; $index++) {
            $course = $this->createCourse($lecturer, 'PAGE'.str_pad((string) $index, 3, '0', STR_PAD_LEFT));
            $course->students()->attach($student->id, ['enrolled_at' => now()]);
        }

        $this->actingAs($student, 'sanctum');
        $firstPage = $this->getJson('/api/v1/my/courses?page=1')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 21)
            ->assertJsonCount(20, 'data');
        $secondPage = $this->getJson('/api/v1/my/courses?page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(1, 'data');

        $firstPageIds = array_column($firstPage->json('data'), 'id');
        $secondPageIds = array_column($secondPage->json('data'), 'id');
        $this->assertSame([], array_intersect($firstPageIds, $secondPageIds));
        $this->assertCount(21, array_unique(array_merge($firstPageIds, $secondPageIds)));
    }

    public function test_course_detail_and_detail_resources_work_for_all_authorized_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'DETAIL101');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        foreach ([$admin, $lecturer, $student] as $user) {
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/v1/courses/'.$course->id)
                ->assertOk()
                ->assertJsonPath('data.id', $course->id)
                ->assertJsonPath('data.lecturer.id', $lecturer->id);

            $this->getJson('/api/v1/courses/'.$course->id.'/assignments')
                ->assertOk()
                ->assertJsonPath('meta.per_page', 20);
            $this->getJson('/api/v1/courses/'.$course->id.'/materials')
                ->assertOk()
                ->assertJsonPath('meta.per_page', 20);
        }

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/students')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 20);
        $this->getJson('/api/v1/courses/'.$course->id.'/enrollments/candidates')
            ->assertOk()
            ->assertJsonPath('data.course.id', $course->id);
    }

    public function test_course_material_and_assignment_lists_paginate_independently(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'PAGE201');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        for ($index = 1; $index <= 21; $index++) {
            $material = new Material;
            $material->course_id = $course->id;
            $material->uploaded_by = $lecturer->id;
            $material->title = 'Materi '.$index;
            $material->description = '';
            $material->type = 'file';
            $material->file_path = 'private/materials/'.$index.'.pdf';
            $material->original_name = $index.'.pdf';
            $material->file_size = 100;
            $material->mime_type = 'application/pdf';
            $material->save();
            $this->createAssignment($course, $lecturer, 'published');
        }

        $this->actingAs($student, 'sanctum');
        $firstMaterials = $this->getJson('/api/v1/courses/'.$course->id.'/materials?page=1')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(20, 'data');
        $secondMaterials = $this->getJson('/api/v1/courses/'.$course->id.'/materials?page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(1, 'data');

        $firstAssignments = $this->getJson('/api/v1/courses/'.$course->id.'/assignments?page=1')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(20, 'data');
        $secondAssignments = $this->getJson('/api/v1/courses/'.$course->id.'/assignments?page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(1, 'data');

        foreach ([
            [$firstMaterials->json('data'), $secondMaterials->json('data')],
            [$firstAssignments->json('data'), $secondAssignments->json('data')],
        ] as [$firstPage, $secondPage]) {
            $firstPageIds = array_column($firstPage, 'id');
            $secondPageIds = array_column($secondPage, 'id');
            $this->assertSame([], array_intersect($firstPageIds, $secondPageIds));
            $this->assertCount(21, array_unique(array_merge($firstPageIds, $secondPageIds)));
        }
    }

    public function test_material_and_assignment_collections_use_resources_and_hide_drafts(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API201');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $material = new Material;
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
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API250');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $path = UploadedFile::fake()->create('slide.pdf', 10, 'application/pdf')
            ->store('materials', 'local');

        $material = new Material;
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

        foreach ([
            [['score' => -1], 'score', 'min'],
            [['score' => 101], 'score', 'max'],
            [['score' => 'bukan angka'], 'score', 'numeric'],
            [['score' => 50, 'feedback' => []], 'feedback', 'string'],
            [[], 'score', 'required'],
        ] as [$payload, $field, $rule]) {
            $this->putJson('/api/v1/submissions/'.$submissionId.'/grade', $payload)
                ->assertUnprocessable()
                ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
                ->assertJsonStructure(['errors' => [$field]])
                ->assertJsonPath('errors.'.$field.'.0', fn ($message) => is_string($message) && $message !== '');
        }
        $this->assertDatabaseCount('grades', 0);

        $grade = $this->putJson('/api/v1/submissions/'.$submissionId.'/grade', [
            'score' => 0,
        ])->assertCreated()
            ->assertJsonPath('data.score', '0.00')
            ->assertJsonPath('data.feedback', null)
            ->assertJsonPath('data.graded_by', $lecturer->id)
            ->assertJsonPath('data.graded_at', fn ($value) => $value !== null);
        $gradeId = $grade->json('data.id');

        $this->getJson('/api/v1/grades/'.$gradeId)
            ->assertOk()
            ->assertJsonPath('data.id', $submissionId)
            ->assertJsonPath('data.grade.id', $gradeId)
            ->assertJsonPath('data.assignment.course.code', 'API301');

        $this->postJson('/api/v1/grades/'.$gradeId.'/publish')
            ->assertOk()
            ->assertJsonPath('data.published_at', fn ($value) => $value !== null);

        $this->putJson('/api/v1/submissions/'.$submissionId.'/grade', [
            'score' => 100,
            'feedback' => 'Revisi nilai',
        ])->assertOk()
            ->assertJsonPath('data.id', $gradeId)
            ->assertJsonPath('data.score', '100.00')
            ->assertJsonPath('data.published_at', null)
            ->assertJsonPath('data.graded_by', $lecturer->id);

        $this->getJson('/api/v1/assignments/'.$assignmentId.'/submissions')
            ->assertOk()
            ->assertJsonPath('data.0.student.id', $student->id)
            ->assertJsonPath('data.0.grade.score', '100.00')
            ->assertJsonMissingPath('data.0.file_path');

        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseHas('grades', [
            'id' => $gradeId,
            'submission_id' => $submissionId,
            'graded_by' => $lecturer->id,
            'score' => '100.00',
            'feedback' => 'Revisi nilai',
            'published_at' => null,
        ]);

        $this->patchJson('/api/v1/assignments/'.$assignmentId, ['title' => 'Tugas Revisi'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Tugas Revisi');
        $this->deleteJson('/api/v1/assignments/'.$assignmentId)
            ->assertForbidden();
        $this->assertDatabaseHas('assignments', ['id' => $assignmentId]);
    }

    
    public function test_my_submissions_are_scoped_and_permissions_use_batched_relations(): void
    {
    	$lecturer = User::factory()->dosen()->create();
    	$student = User::factory()->create();
    	$otherStudent = User::factory()->create();

    	$courses = collect([
            $this->createCourse($lecturer, 'API302'),
            $this->createCourse($lecturer, 'API304'),
            $this->createCourse($lecturer, 'API305'),
   	]);

    	foreach ($courses as $course) {
            $course->students()->attach(
                [$student->id, $otherStudent->id],
                ['enrolled_at' => now()]
            );
        }

        $ownSubmissions = [];

        for ($index = 0; $index < 10; $index++) {
            $course = $courses[$index % $courses->count()];
            $assignment = $this->createAssignment($course, $lecturer, 'published');

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'file_path' => "submissions/{$assignment->id}/answer.pdf",
                'original_name' => 'answer.pdf',
                'file_size' => 100,
                'submitted_at' => now()->subMinutes($index),
            ]);

            $ownSubmissions[] = $submission;

            if ($index === 1) {
                Grade::create([
                    'submission_id' => $submission->id,
                    'graded_by' => $lecturer->id,
                    'score' => 80,
                    'graded_at' => now(),
                ]);
            }
        }

        $otherAssignment = $this->createAssignment($courses[0], $lecturer, 'published');

        $otherSubmission = Submission::create([
            'assignment_id' => $otherAssignment->id,
            'user_id' => $otherStudent->id,
            'file_path' => "submissions/{$otherAssignment->id}/other.pdf",
            'original_name' => 'other.pdf',
            'file_size' => 100,
            'submitted_at' => now(),
        ]);

        $queries = ['grades' => [], 'course_user' => []];

        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $sql = strtolower($query->sql);

            if (preg_match('/\bfrom\s+[`"]?grades[`"]?\b/', $sql)) {
                $queries['grades'][] = $query->sql;
            }

            if (str_contains($sql, 'course_user')) {
                $queries['course_user'][] = $query->sql;
            }
        });

        $response = $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/my/submissions?page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 10);

        $rows = collect($response->json('data'))->keyBy('id');

        $this->assertCount(10, $rows);

        $this->assertEqualsCanonicalizing(
            array_map(fn (Submission $submission) => $submission->id, $ownSubmissions),
            $rows->keys()->all(),
        );

        $this->assertArrayNotHasKey($otherSubmission->id, $rows->all());
        $this->assertTrue($rows[$ownSubmissions[0]->id]['permissions']['update']);
        $this->assertFalse($rows[$ownSubmissions[1]->id]['permissions']['update']);

        $this->assertSame(1, count($queries['grades']), 'Grades SQL: '.json_encode($queries['grades']));
        $this->assertSame(1, count($queries['course_user']), 'Course membership SQL: '.json_encode($queries['course_user']));

        $policy = app(SubmissionPolicy::class);

        $this->assertTrue($policy->update($student, $ownSubmissions[0]->fresh()));
        $this->assertFalse($policy->update($student, $ownSubmissions[1]->fresh()));
    }

    public function test_submission_replacement_requires_an_ungraded_enrolled_owner(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer, 'API303');
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);
        $assignment = $this->createAssignment($course, $lecturer, 'published');
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/answer.pdf',
            'original_name' => 'answer.pdf',
            'file_size' => 100,
            'submitted_at' => now(),
        ]);

        $gradedAssignment = $this->createAssignment($course, $lecturer, 'published');
        $gradedSubmission = Submission::create([
            'assignment_id' => $gradedAssignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/graded.pdf',
            'original_name' => 'graded.pdf',
            'file_size' => 100,
            'submitted_at' => now(),
        ]);
        Grade::create([
            'submission_id' => $gradedSubmission->id,
            'graded_by' => $lecturer->id,
            'score' => 80,
            'graded_at' => now(),
        ]);

        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments/'.$assignment->id.'/submissions', [
                'file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.original_name', 'replacement.pdf');

        $this->post('/api/v1/assignments/'.$gradedAssignment->id.'/submissions', [
            'file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertForbidden();

        $this->actingAs($otherStudent, 'sanctum')
            ->getJson('/api/v1/submissions/'.$submission->id)
            ->assertForbidden();
        $this->post('/api/v1/assignments/'.$assignment->id.'/submissions', [
            'file' => UploadedFile::fake()->create('other.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'user_id' => $student->id,
            'original_name' => 'replacement.pdf',
        ]);

        $course->students()->detach($student->id);
        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments/'.$assignment->id.'/submissions', [
                'file' => UploadedFile::fake()->create('unenrolled.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])->assertForbidden();
        $this->getJson('/api/v1/my/submissions?page=1')
            ->assertOk()
            ->assertJsonPath('data.0.permissions.update', false);
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
        $course = new Course;
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
        $assignment = new Assignment;
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
