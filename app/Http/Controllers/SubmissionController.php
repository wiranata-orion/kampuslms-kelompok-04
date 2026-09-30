<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    // ------- Untuk mahasiswa -------

    public function create(Request $request, Assignment $assignment): View|RedirectResponse
    {
        $this->authorizeStudent($request, $assignment->course);

        abort_if($assignment->status !== 'published', 403, 'Tugas ini belum dipublikasikan.');

        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->route('mahasiswa.submissions.edit', $existing);
        }

        return view('submissions.create', ['title' => 'Kumpulkan Tugas', 'assignment' => $assignment]);
    }

    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorizeStudent($request, $assignment->course);

        abort_if($assignment->status !== 'published', 403, 'Tugas ini belum dipublikasikan.');

        if (! $assignment->allow_late && now()->greaterThan($assignment->due_at)) {
            return back()->withErrors(['file' => 'Batas waktu pengumpulan sudah lewat.']);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'note' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');

        $submission = new Submission();
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $request->user()->id;
        $submission->file_path = $file->store('submissions', 'public');
        $submission->original_name = $file->getClientOriginalName();
        $submission->file_size = $file->getSize();
        $submission->note = $validated['note'] ?? null;
        // submitted_at & is_late di-set otomatis lewat model event di AppServiceProvider
        $submission->save();

        return redirect()
            ->route('mahasiswa.submissions.show', $submission)
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function show(Request $request, Submission $submission): View
    {
        $this->authorizeStudentSubmission($request, $submission);

        return view('submissions.show', ['title' => 'Pengumpulan Saya', 'submission' => $submission]);
    }

    public function edit(Request $request, Submission $submission): View
    {
        $this->authorizeStudentSubmission($request, $submission);

        return view('submissions.edit', ['title' => 'Edit Pengumpulan', 'submission' => $submission]);
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeStudentSubmission($request, $submission);

        $assignment = $submission->assignment;

        if (! $assignment->allow_late && now()->greaterThan($assignment->due_at)) {
            return back()->withErrors(['file' => 'Batas waktu pengumpulan sudah lewat, tidak bisa diubah lagi.']);
        }

        $validated = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'note' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($submission->file_path);

            $file = $request->file('file');
            $submission->file_path = $file->store('submissions', 'public');
            $submission->original_name = $file->getClientOriginalName();
            $submission->file_size = $file->getSize();
            $submission->submitted_at = now();
            $submission->is_late = now()->greaterThan($assignment->due_at);
        }

        $submission->note = $validated['note'] ?? $submission->note;
        $submission->save();

        return redirect()
            ->route('mahasiswa.submissions.show', $submission)
            ->with('success', 'Pengumpulan berhasil diperbarui.');
    }

    // ------- Untuk dosen -------

    public function indexForDosen(Request $request, Assignment $assignment): View
    {
        $this->authorizeLecturer($request, $assignment->course);

        $submissions = $assignment->submissions()->with(['student', 'grade'])->paginate(15);

        return view('submissions.dosen-index', [
            'title' => 'Pengumpulan Tugas',
            'assignment' => $assignment,
            'submissions' => $submissions,
        ]);
    }

    public function showForDosen(Request $request, Submission $submission): View
    {
        $this->authorizeLecturer($request, $submission->assignment->course);

        $submission->load(['student', 'grade']);

        return view('submissions.dosen-show', ['title' => 'Detail Pengumpulan', 'submission' => $submission]);
    }

    private function authorizeLecturer(Request $request, Course $course): void
    {
        abort_unless(
            $course->lecturer_id === $request->user()->id,
            403,
            'Kamu bukan dosen pengampu mata kuliah ini.'
        );
    }

    private function authorizeStudent(Request $request, Course $course): void
    {
        abort_unless(
            $course->students()->where('users.id', $request->user()->id)->exists(),
            403,
            'Kamu tidak terdaftar pada mata kuliah ini.'
        );
    }

    private function authorizeStudentSubmission(Request $request, Submission $submission): void
    {
        $this->authorizeStudent($request, $submission->assignment->course);

        abort_unless(
            $submission->user_id === $request->user()->id,
            403,
            'Kamu tidak memiliki izin untuk mengakses pengumpulan ini.'
        );
    }
}
