<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = match ($user->role) {
            'admin' => [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
            ],
            'dosen' => [
                'total_courses' => $user->taughtCourses()->count(),
                'total_assignments' => $user->taughtCourses()
                    ->withCount('assignments')
                    ->get()
                    ->sum('assignments_count'),
            ],
            'mahasiswa' => [
                'total_courses' => $user->courses()->count(),
                'total_submissions' => $user->submissions()->count(),
            ],
            default => [],
        };

        return response()->json([
            'data' => [
                'user' => (new UserResource($user))->resolve($request),
                'stats' => $stats,
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $query = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->query('role')))
            ->orderBy('name');

        return ApiResponse::collection($query->paginate(15), UserResource::class, $request);
    }

    public function storeUser(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', 'unique:users,nim_nip'],
        ]);

        $user = User::create($validated);

        return response()->json([
            'data' => (new UserResource($user))->resolve($request),
        ], 201);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', Rule::unique('users', 'nim_nip')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ]);

        $user->fill(collect($validated)->reject(fn ($value, $key) => $key === 'password' && ! $value)->all());
        $user->save();

        return response()->json([
            'data' => (new UserResource($user))->resolve($request),
        ]);
    }

    public function showUser(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        return response()->json(['data' => (new UserResource($user))->resolve($request)]);
    }

    public function destroyUser(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        abort_if($user->is($request->user()), 403, 'Kamu tidak bisa menghapus akunmu sendiri.');
        $user->delete();

        return response()->json(['data' => ['message' => 'Pengguna berhasil dihapus.']]);
    }

    public function lecturers(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);

        return response()->json([
            'data' => UserResource::collection(User::where('role', 'dosen')->orderBy('name')->get())->resolve($request),
        ]);
    }

    public function storeCourse(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'dosen')],
            'status' => ['required', 'in:draft,active,archived'],
        ]);

        $course = new Course();
        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();
        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return response()->json(['data' => (new CourseResource($course))->resolve($request)], 201);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('courses', 'code')->ignore($course->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'dosen')],
            'status' => ['required', 'in:draft,active,archived'],
        ]);

        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();
        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return response()->json(['data' => (new CourseResource($course))->resolve($request)]);
    }

    public function destroyCourse(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $course->delete();

        return response()->json(['data' => ['message' => 'Mata kuliah berhasil dihapus.']]);
    }

    public function storeMaterial(Request $request, Course $course): JsonResponse
    {
        $this->authorizeLecturer($request, $course);
        $validated = $this->validateMaterial($request, true);

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $request->user()->id;
        $this->fillMaterial($request, $material, $validated);
        $material->save();
        $material->load('uploader');

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)], 201);
    }

    public function showMaterial(Request $request, Material $material): JsonResponse
    {
        $this->authorizeCourseAccess($request, $material->course);
        $material->load(['uploader', 'course']);

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)]);
    }

    public function downloadMaterial(Request $request, Material $material)
    {
        $this->authorizeCourseAccess($request, $material->course);
        abort_unless($material->file_path, 404, 'File materi tidak tersedia.');

        return Storage::disk('public')->download(
            $material->file_path,
            $material->original_name ?: basename($material->file_path)
        );
    }

    public function updateMaterial(Request $request, Material $material): JsonResponse
    {
        $this->authorizeLecturer($request, $material->course);
        $validated = $this->validateMaterial($request, false);
        $this->fillMaterial($request, $material, $validated);
        $material->save();
        $material->load('uploader');

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)]);
    }

    public function destroyMaterial(Request $request, Material $material): JsonResponse
    {
        $this->authorizeLecturer($request, $material->course);
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();

        return response()->json(['data' => ['message' => 'Materi berhasil dihapus.']]);
    }

    public function enrollmentCandidates(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $enrolledIds = $course->students()->pluck('users.id');
        $students = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'course' => (new CourseResource($course->load('lecturer')))->resolve($request),
                'candidates' => UserResource::collection($students)->resolve($request),
            ],
        ]);
    }

    public function courseStudents(Request $request, Course $course): JsonResponse
    {
        abort_unless($request->user()->role === 'admin', 403);
        $students = $course->students()->orderBy('name')->paginate(20);

        return ApiResponse::collection($students, UserResource::class, $request);
    }

    public function storeEnrollment(Request $request, Course $course): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'dosen'], true), 403);
        if ($request->user()->role === 'dosen') {
            $this->authorizeLecturer($request, $course);
        }
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mahasiswa')],
        ]);
        abort_if($course->students()->whereKey($validated['user_id'])->exists(), 409, 'Mahasiswa sudah terdaftar di mata kuliah ini.');
        $course->students()->attach($validated['user_id'], ['enrolled_at' => now()]);

        return response()->json(['data' => ['message' => 'Mahasiswa berhasil didaftarkan.']], 201);
    }

    public function destroyEnrollment(Request $request, Course $course, User $user): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'dosen'], true), 403);
        if ($request->user()->role === 'dosen') {
            $this->authorizeLecturer($request, $course);
        }
        abort_unless($user->role === 'mahasiswa', 404);
        $course->students()->detach($user->id);

        return response()->json(['data' => ['message' => 'Mahasiswa dikeluarkan dari mata kuliah.']]);
    }

    private function validateMaterial(Request $request, bool $creating): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:file,link'],
            'file' => [$creating ? 'required_if:type,file' : 'nullable', 'file', 'max:10240'],
            'external_url' => ['required_if:type,link', 'nullable', 'url'],
        ]);
    }

    private function fillMaterial(Request $request, Material $material, array $validated): void
    {
        $material->title = $validated['title'];
        $material->description = $validated['description'] ?? '';
        $material->type = $validated['type'];

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $file = $request->file('file');
            $material->file_path = $file->store('materials', 'public');
            $material->original_name = $file->getClientOriginalName();
            $material->file_size = $file->getSize();
            $material->mime_type = $file->getMimeType();
            $material->external_url = null;
        } elseif ($validated['type'] === 'link') {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $material->external_url = $validated['external_url'];
            $material->file_path = null;
            $material->original_name = null;
            $material->file_size = null;
            $material->mime_type = null;
        }
    }

    private function authorizeLecturer(Request $request, Course $course): void
    {
        abort_unless($request->user()->role === 'dosen' && $course->lecturer_id === $request->user()->id, 403);
    }

    private function authorizeCourseAccess(Request $request, Course $course): void
    {
        $user = $request->user();
        $allowed = match ($user->role) {
            'admin' => true,
            'dosen' => $course->lecturer_id === $user->id,
            'mahasiswa' => $course->students()->whereKey($user->id)->exists(),
            default => false,
        };
        abort_unless($allowed, 403);
    }
}
