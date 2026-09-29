<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request, Course $course): View
    {
        $this->authorizeLecturer($request, $course);

        $materials = $course->materials()->with('uploader')->latest()->paginate(15);

        return view('materials.index', [
            'title' => 'Materi - '.$course->name,
            'course' => $course,
            'materials' => $materials,
        ]);
    }

    public function create(Request $request, Course $course): View
    {
        $this->authorizeLecturer($request, $course);

        return view('materials.create', [
            'title' => 'Tambah Materi',
            'course' => $course,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeLecturer($request, $course);

        $validated = $this->validatedInput($request);

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $request->user()->id;
        $material->title = $validated['title'];
        $material->description = $validated['description'] ?? '';
        $material->type = $validated['type'];

        $this->applyFileOrLink($request, $material, $validated);
        $material->save();

        return redirect()
            ->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Request $request, Material $material): View
    {
        $this->authorizeLecturer($request, $material->course);

        return view('materials.show', [
            'title' => $material->title,
            'material' => $material,
        ]);
    }

    public function edit(Request $request, Material $material): View
    {
        $this->authorizeLecturer($request, $material->course);

        return view('materials.edit', [
            'title' => 'Edit Materi',
            'material' => $material,
        ]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->authorizeLecturer($request, $material->course);

        $validated = $this->validatedInput($request);

        $material->title = $validated['title'];
        $material->description = $validated['description'] ?? '';
        $material->type = $validated['type'];

        $this->applyFileOrLink($request, $material, $validated);
        $material->save();

        return redirect()
            ->route('dosen.materials.show', $material)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Material $material): RedirectResponse
    {
        $this->authorizeLecturer($request, $material->course);

        $courseId = $material->course_id;

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()
            ->route('dosen.courses.materials.index', $courseId)
            ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Titik rawan IDOR: pastikan course ini benar diampu oleh dosen yang
     * sedang login. Sementara pakai abort_unless() sesuai kesepakatan —
     * Policy menyusul.
     */
    private function authorizeLecturer(Request $request, Course $course): void
    {
        abort_unless(
            $course->lecturer_id === $request->user()->id,
            403,
            'Kamu bukan dosen pengampu mata kuliah ini.'
        );
    }

    private function validatedInput(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:file,link'],
            'file' => ['required_if:type,file', 'nullable', 'file', 'max:10240'],
            'external_url' => ['required_if:type,link', 'nullable', 'url'],
        ]);
    }

    private function applyFileOrLink(Request $request, Material $material, array $validated): void
    {
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
}