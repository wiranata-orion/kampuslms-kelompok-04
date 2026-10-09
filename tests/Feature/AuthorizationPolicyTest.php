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

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_index_is_scoped_in_the_query_even_with_scope_all(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $activeCourse = $this->createCourse($lecturer, 'AUTH101');
        $archivedCourse = $this->createCourse($lecturer, 'AUTH102', 'archived');
        $otherCourse = $this->createCourse($otherLecturer, 'AUTH103');
        $activeCourse->students()->attach($student->id, ['enrolled_at' => now()]);
        $archivedCourse->students()->attach($student->id, ['enrolled_at' => now()]);

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses?scope=all')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/courses?scope=all')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $activeCourse->id);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/courses?scope=all')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);

        $this->assertNotSame($activeCourse->id, $otherCourse->id);
    }

    public function test_student_cannot_read_nonmember_content_archived_courses_or_draft_assignments(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $outsider = User::factory()->create();
        $course = $this->createCourse($lecturer, 'AUTH201');
        $archived = $this->createCourse($lecturer, 'AUTH202', 'archived');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $material = $this->createMaterial($course, $lecturer);
        $draft = $this->createAssignment($course, $lecturer, 'draft');

        $this->actingAs($outsider, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id)
            ->assertForbidden();

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/courses/'.$archived->id)
            ->assertForbidden();
        $this->getJson('/api/v1/courses/'.$course->id.'/assignments')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/assignments/'.$draft->id)
            ->assertForbidden();
        $this->getJson('/api/v1/materials/'.$material->id)
            ->assertOk();

        $this->actingAs($outsider, 'sanctum')
            ->getJson('/api/v1/materials/'.$material->id)
            ->assertForbidden();
    }

    public function test_admin_deletion_is_blocked_when_course_or_assignment_has_restricted_data(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'AUTH301');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = $this->createAssignment($course, $lecturer, 'published');
        $submission = $this->createSubmission($assignment, $student);
        $this->createGrade($submission, $lecturer);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/courses/'.$course->id)
            ->assertForbidden();
        $this->deleteJson('/api/v1/assignments/'.$assignment->id)
            ->assertForbidden();
    }

    public function test_enrollment_management_is_limited_to_admin_and_the_course_lecturer(): void
    {
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'AUTH350');

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/students')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
        $this->getJson('/api/v1/courses/'.$course->id.'/enrollments/candidates')
            ->assertOk()
            ->assertJsonPath('data.candidates.0.id', $student->id);
        $this->postJson('/api/v1/courses/'.$course->id.'/enrollments', ['user_id' => $student->id])
            ->assertCreated();

        $this->actingAs($otherLecturer, 'sanctum')
            ->getJson('/api/v1/courses/'.$course->id.'/students')
            ->assertForbidden();
    }

    public function test_submission_cross_role_idor_draft_duplicate_and_no_revision_scenarios(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer, 'AUTH401');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $published = $this->createAssignment($course, $lecturer, 'published');
        $draft = $this->createAssignment($course, $lecturer, 'draft');

        $this->actingAs($otherStudent, 'sanctum')
            ->post('/api/v1/assignments/'.$published->id.'/submissions', [
                'file' => UploadedFile::fake()->create('outside.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertForbidden();

        foreach ([$lecturer, $admin] as $forbiddenRole) {
            $this->actingAs($forbiddenRole, 'sanctum')
                ->post('/api/v1/assignments/'.$published->id.'/submissions', [
                    'file' => UploadedFile::fake()->create('role.pdf', 10, 'application/pdf'),
                ], ['Accept' => 'application/json'])
                ->assertForbidden();
        }

        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments/'.$draft->id.'/submissions', [
                'file' => UploadedFile::fake()->create('draft.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertForbidden();

        $response = $this->post('/api/v1/assignments/'.$published->id.'/submissions', [
            'file' => UploadedFile::fake()->create('answer.pdf', 10, 'application/pdf'),
            'note' => 'Jawaban',
        ], ['Accept' => 'application/json'])->assertCreated();
        $submissionId = $response->json('data.id');

        $this->post('/api/v1/assignments/'.$published->id.'/submissions', [
            'file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertForbidden();
        $this->putJson('/api/v1/submissions/'.$submissionId, ['note' => 'Revisi'])
            ->assertForbidden();

        $this->actingAs($otherStudent, 'sanctum')
            ->getJson('/api/v1/submissions/'.$submissionId)
            ->assertForbidden();
        $this->get('/api/v1/submissions/'.$submissionId.'/download')
            ->assertForbidden();

        $this->actingAs($otherLecturer, 'sanctum')
            ->getJson('/api/v1/submissions/'.$submissionId)
            ->assertForbidden();
        $this->getJson('/api/v1/assignments/'.$published->id.'/submissions')
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->getJson('/api/v1/submissions/'.$submissionId)
            ->assertOk();
        $this->get('/api/v1/submissions/'.$submissionId.'/download')
            ->assertOk()
            ->assertDownload('answer.pdf');
        $this->getJson('/api/v1/assignments/'.$published->id.'/submissions')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/submissions/'.$submissionId)
            ->assertOk();
        $this->get('/api/v1/submissions/'.$submissionId.'/download')
            ->assertOk()
            ->assertDownload('answer.pdf');
    }

    public function test_deadline_validation_is_422_and_grade_visibility_requires_publication(): void
    {
        Storage::fake('local');
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $course = $this->createCourse($lecturer, 'AUTH501');
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);
        $lateAssignment = $this->createAssignment($course, $lecturer, 'published', false, now()->subMinute());
        $this->actingAs($student, 'sanctum')
            ->post('/api/v1/assignments/'.$lateAssignment->id.'/submissions', [
                'file' => UploadedFile::fake()->create('late.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();

        $assignment = $this->createAssignment($course, $lecturer, 'published');
        $submission = $this->createSubmission($assignment, $student);

        $this->actingAs($otherLecturer, 'sanctum')
            ->putJson('/api/v1/submissions/'.$submission->id.'/grade', ['score' => 75])
            ->assertForbidden();

        $grade = $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/submissions/'.$submission->id.'/grade', ['score' => 75])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/grades/'.$grade)
            ->assertForbidden();
        $this->getJson('/api/v1/submissions/'.$submission->id.'/grade')
            ->assertForbidden();

        $this->actingAs($otherStudent, 'sanctum')
            ->postJson('/api/v1/grades/'.$grade.'/publish')
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/grades/'.$grade.'/publish')
            ->assertOk()
            ->assertJsonPath('data.published_at', fn ($value) => $value !== null);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/grades/'.$grade)
            ->assertOk()
            ->assertJsonPath('data.grade.id', $grade)
            ->assertJsonPath('data.grade.permissions.view', true);

        $this->actingAs($otherStudent, 'sanctum')
            ->getJson('/api/v1/grades/'.$grade)
            ->assertForbidden();
    }

    public function test_user_policy_allows_self_profile_but_protects_roles_and_other_accounts(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $lecturer = User::factory()->dosen()->create();
        $course = $this->createCourse($lecturer, 'AUTH601');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/v1/users/'.$student->id)
            ->assertOk();
        $this->getJson('/api/v1/users/'.$otherStudent->id)
            ->assertForbidden();

        $this->putJson('/api/v1/users/'.$student->id, [
            'name' => 'Nama Diperbarui',
            'email' => $student->email,
        ])->assertOk()->assertJsonPath('data.name', 'Nama Diperbarui');

        $this->patchJson('/api/v1/me', ['name' => 'Profil Saya'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Profil Saya');

        $this->patchJson('/api/v1/me', ['role' => 'admin'])
            ->assertForbidden();

        $this->putJson('/api/v1/users/'.$student->id, [
            'name' => 'Nama Diperbarui',
            'email' => $student->email,
            'role' => 'admin',
        ])->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/users/'.$admin->id)
            ->assertForbidden();
        $this->deleteJson('/api/v1/users/'.$lecturer->id)
            ->assertForbidden();

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_material_crud_is_course_scoped_and_stores_file_metadata_without_uploading(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'MAT701');
        $otherCourse = $this->createCourse($otherLecturer, 'MAT702');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $metadata = [
            'title' => 'Slide pengantar',
            'description' => 'Materi minggu pertama',
            'type' => 'file',
            'original_name' => 'pengantar.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
        ];

        $created = $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$course->id.'/materials', $metadata)
            ->assertCreated()
            ->assertJsonPath('data.original_name', 'pengantar.pdf')
            ->assertJsonPath('data.download_available', false)
            ->assertJsonMissingPath('data.file_path');
        $materialId = $created->json('data.id');
        Material::whereKey($materialId)->update(['uploaded_by' => $otherLecturer->id]);

        $this->assertDatabaseHas('materials', [
            'id' => $materialId,
            'file_path' => null,
            'original_name' => 'pengantar.pdf',
            'file_size' => 2048,
        ]);

        $this->getJson('/api/v1/materials/'.$materialId)
            ->assertOk()
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', true);

        $this->actingAs($otherLecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$course->id.'/materials', $metadata)
            ->assertForbidden();
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/v1/courses/'.$course->id.'/materials', $metadata)
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/courses/'.$otherCourse->id.'/materials', $metadata)
            ->assertForbidden();
        $this->putJson('/api/v1/materials/'.$materialId, [...$metadata, 'title' => 'Metadata diperbarui'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Metadata diperbarui');

        $this->actingAs($otherLecturer, 'sanctum')
            ->putJson('/api/v1/materials/'.$materialId, $metadata)
            ->assertForbidden();
        $this->actingAs($student, 'sanctum')
            ->deleteJson('/api/v1/materials/'.$materialId)
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/materials/'.$materialId)
            ->assertOk();

        $adminMaterial = $this->postJson('/api/v1/courses/'.$course->id.'/materials', [
            'title' => 'Tautan referensi',
            'description' => 'Sumber tambahan',
            'type' => 'link',
            'external_url' => 'https://example.test/reference',
        ])->assertCreated();
        $adminMaterialId = $adminMaterial->json('data.id');
        $this->putJson('/api/v1/materials/'.$adminMaterialId, [
            'title' => 'Tautan diperbarui',
            'description' => 'Sumber tambahan',
            'type' => 'link',
            'external_url' => 'https://example.test/reference-updated',
        ])->assertOk()->assertJsonPath('data.title', 'Tautan diperbarui');
        $this->deleteJson('/api/v1/materials/'.$adminMaterialId)->assertOk();

        $this->actingAs($lecturer, 'sanctum')
            ->post('/api/v1/courses/'.$course->id.'/materials', [
                ...$metadata,
                'file' => UploadedFile::fake()->create('upload.pdf', 2, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();
    }

    public function test_assignment_crud_is_course_scoped_and_delete_only_checks_for_grades(): void
    {
        $admin = User::factory()->admin()->create();
        $lecturer = User::factory()->dosen()->create();
        $otherLecturer = User::factory()->dosen()->create();
        $student = User::factory()->create();
        $course = $this->createCourse($lecturer, 'TUG701');
        $otherCourse = $this->createCourse($otherLecturer, 'TUG702');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $input = [
            'course_id' => $course->id,
            'title' => 'Tugas awal',
            'instructions' => 'Kerjakan materi pertemuan pertama.',
            'due_at' => now()->addWeek()->toDateTimeString(),
            'max_score' => 100,
            'allow_late' => false,
            'status' => 'published',
        ];
        $updateInput = $input;
        unset($updateInput['course_id']);

        $created = $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/assignments', $input)
            ->assertCreated();
        $assignmentId = $created->json('data.id');

        $this->getJson('/api/v1/assignments/'.$assignmentId)
            ->assertOk()
            ->assertJsonPath('data.permissions.can_update', true)
            ->assertJsonPath('data.permissions.can_delete', true);

        $this->postJson('/api/v1/assignments', [...$input, 'course_id' => $otherCourse->id])
            ->assertForbidden();
        $this->actingAs($student, 'sanctum')
            ->postJson('/api/v1/assignments', $input)
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/assignments/'.$assignmentId, [...$input, 'course_id' => $otherCourse->id])
            ->assertUnprocessable();
        $this->actingAs($otherLecturer, 'sanctum')
            ->putJson('/api/v1/assignments/'.$assignmentId, $updateInput)
            ->assertForbidden();

        $this->actingAs($lecturer, 'sanctum')
            ->putJson('/api/v1/assignments/'.$assignmentId, [...$updateInput, 'title' => 'Tugas diperbarui'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Tugas diperbarui');

        $ungraded = $this->createAssignment($course, $lecturer, 'published');
        $this->createSubmission($ungraded, $student);
        $this->deleteJson('/api/v1/assignments/'.$ungraded->id)->assertNoContent();

        $graded = $this->createAssignment($course, $lecturer, 'published');
        $submission = $this->createSubmission($graded, $student);
        $this->createGrade($submission, $lecturer);
        $this->deleteJson('/api/v1/assignments/'.$graded->id)->assertForbidden();

        $adminAssignment = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/assignments', [...$input, 'course_id' => $otherCourse->id])
            ->assertCreated();
        $adminAssignmentId = $adminAssignment->json('data.id');
        $this->putJson('/api/v1/assignments/'.$adminAssignmentId, [...$updateInput, 'title' => 'Admin mengubah'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Admin mengubah');
        $this->deleteJson('/api/v1/assignments/'.$adminAssignmentId)->assertNoContent();
    }

    private function createCourse(User $lecturer, string $code, string $status = 'active'): Course
    {
        $course = new Course;
        $course->code = $code;
        $course->name = 'Mata Kuliah '.$code;
        $course->description = 'Deskripsi';
        $course->sks = 3;
        $course->lecturer_id = $lecturer->id;
        $course->status = $status;
        $course->save();

        return $course;
    }

    private function createAssignment(
        Course $course,
        User $lecturer,
        string $status,
        bool $allowLate = true,
        mixed $dueAt = null,
    ): Assignment {
        $assignment = new Assignment;
        $assignment->course_id = $course->id;
        $assignment->created_by = $lecturer->id;
        $assignment->title = 'Tugas '.$course->code.' '.$status;
        $assignment->instructions = 'Instruksi';
        $assignment->due_at = $dueAt ?? now()->addDay();
        $assignment->max_score = 100;
        $assignment->allow_late = $allowLate;
        $assignment->status = $status;
        $assignment->save();

        return $assignment;
    }

    private function createMaterial(Course $course, User $uploader): Material
    {
        $material = new Material;
        $material->course_id = $course->id;
        $material->uploaded_by = $uploader->id;
        $material->title = 'Materi '.$course->code;
        $material->description = 'Materi uji';
        $material->type = 'link';
        $material->external_url = 'https://example.test/material';
        $material->save();

        return $material;
    }

    private function createSubmission(Assignment $assignment, User $student): Submission
    {
        $submission = new Submission;
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $student->id;
        $submission->file_path = 'submissions/answer.pdf';
        $submission->original_name = 'answer.pdf';
        $submission->file_size = 1024;
        $submission->note = null;
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
            'feedback' => 'Baik',
            'graded_at' => now(),
        ]);
    }
}
