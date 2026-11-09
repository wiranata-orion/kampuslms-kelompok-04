<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

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

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::create($validated);

        return response()->json([
            'data' => (new UserResource($user))->resolve($request),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return response()->json(['data' => (new UserResource($user))->resolve($request)]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $user->fill(collect($validated)->reject(fn ($value, $key) => $key === 'password' && ! $value)->all());
        $user->save();

        return response()->json([
            'data' => (new UserResource($user))->resolve($request),
        ]);
    }

    public function updateCurrentUser(Request $request): UserResource
    {
        $user = $request->user();
        Gate::authorize('update', [$user, $request->only('role')]);

        $validated = $request->validate([
            'name' => ['required_without:password', 'string', 'max:150'],
            'password' => ['required_without:name', 'string', 'min:8'],
            'email' => ['prohibited'],
            'role' => ['prohibited'],
            'nim_nip' => ['prohibited'],
        ]);

        $user->fill($validated);
        $user->save();

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        Gate::authorize('delete', $user);
        $user->delete();

        return response()->json(['data' => ['message' => 'Pengguna berhasil dihapus.']]);
    }

    public function lecturers(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return response()->json([
            'data' => UserResource::collection(User::where('role', 'dosen')->orderBy('name')->get())->resolve($request),
        ]);
    }
}
