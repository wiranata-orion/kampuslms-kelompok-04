<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Models\Material;
use App\Support\PrivateFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialApiController extends Controller
{
    public function store(StoreMaterialRequest $request, Course $course): JsonResponse
    {
        $validated = $request->validated();

        $material = new Material;
        $material->course_id = $course->id;
        $material->uploaded_by = $request->user()->id;
        $this->fillMaterial($material, $validated, $request);
        $material->save();
        $material->load('uploader');

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)], 201);
    }

    public function show(Request $request, Material $material): JsonResponse
    {
        Gate::authorize('view', $material);
        $material->load(['uploader', 'course']);

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)]);
    }

    public function download(Request $request, Material $material)
    {
        $material->loadMissing('course');
        Gate::authorize('download', $material);

        abort_unless(
            $material->type === 'file' && filled($material->file_path),
            404,
            'File materi tidak tersedia.'
        );

        return PrivateFiles::response(
            $material->file_path,
            $material->original_name,
            $request->boolean('preview')
        );
    }

    public function update(UpdateMaterialRequest $request, Material $material): JsonResponse
    {
        $validated = $request->validated();
        $this->fillMaterial($material, $validated, $request);
        $material->save();
        $material->load('uploader');

        return response()->json(['data' => (new MaterialResource($material))->resolve($request)]);
    }

    public function destroy(Request $request, Material $material): JsonResponse
    {
        Gate::authorize('delete', $material);
        $material->delete();

        return response()->json(['data' => ['message' => 'Materi berhasil dihapus.']]);
    }

    private function fillMaterial(Material $material, array $validated, ?Request $request = null): void
    {
        $material->title = $validated['title'];
        $material->description = $validated['description'] ?? '';
        $material->type = $validated['type'];

        if ($validated['type'] === 'file') {
            if ($request && $request->hasFile('file')) {
                $file = $request->file('file');
                $material->file_path = $file->store("materials/{$material->course_id}", 'local');
                $material->original_name = $file->getClientOriginalName();
                $material->file_size = $file->getSize();
                $material->mime_type = $file->getMimeType();
            } else {
                $material->original_name = $validated['original_name'] ?? $material->original_name;
                $material->file_size = $validated['file_size'] ?? $material->file_size;
                $material->mime_type = $validated['mime_type'] ?? $material->mime_type;
            }
            $material->external_url = null;
        } elseif ($validated['type'] === 'link') {
            $material->external_url = $validated['external_url'];
            $material->file_path = null;
            $material->original_name = null;
            $material->file_size = null;
            $material->mime_type = null;
        }
    }
}
