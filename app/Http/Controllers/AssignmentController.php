<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course): View
    {
        $this->authorizeLecturer($request, $course);

        $assignments = $course->assignments()->latest('due_at')->paginate(15);

        return view('assignments.index', [
            'title' => 'Tugas - '.$course->name,
            'course' => $course,
            'assignments' => $assignments,
        ]);
    }

    public function create(Request $request, Course $course): View
    {
        $this->authorizeLecturer($request, $course);

        return view('assignments.create', [
            'title' => 'Tambah Tugas',
            'course' => $course,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeLecturer($request, $course);

        $validated = $this->validatedInput($request);

        $assignment = new Assignment();
        $assignment->course_id = $course->id;
        $assignment->created_by = $request->user()->id;
        $assignment->title = $validated['title'];
        $assignment->instructions = $validated['instructions'];
        $assignment->due_at = $validated['due_at'];
        $assignment->max_score = $validated['max_score'] ?? 100;
        $assignment->allow_late = $request->boolean('allow_late');
        $assignment->status = $validated['status'];
        $assignment->save();

        return redirect()
            ->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $this->authorizeLecturer($request, $assignment->course);

        $assignment->load('course');

        return view('assignments.show', [
            'title' => $assignment->title,
            'assignment' => $assignment,
        ]);
    }

    public function edit(Request $request, Assignment $assignment): View
    {
        $this->authorizeLecturer($request, $assignment->course);

        return view('assignments.edit', [
            'title' => 'Edit Tugas',
            'assignment' => $assignment,
        ]);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorizeLecturer($request, $assignment->course);

        $validated = $this->validatedInput($request);

        $assignment->title = $validated['title'];
        $assignment->instructions = $validated['instructions'];
        $assignment->due_at = $validated['due_at'];
        $assignment->max_score = $validated['max_score'] ?? 100;
        $assignment->allow_late = $request->boolean('allow_late');
        $assignment->status = $validated['status'];
        $assignment->save();

        return redirect()
            ->route('dosen.assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorizeLecturer($request, $assignment->course);

        $courseId = $assignment->course_id;
        $assignment->delete();

        return redirect()
            ->route('dosen.courses.assignments.index', $courseId)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /**
     * Titik rawan IDOR: pastikan course ini benar diampu oleh dosen yang
     * sedang login.
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
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['nullable', 'integer', 'between:1,100'],
            'allow_late' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }
}