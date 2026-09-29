<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function create(Request $request, Submission $submission): View|RedirectResponse
    {
        $this->authorizeLecturer($request, $submission->assignment->course);

        if ($submission->grade) {
            return redirect()->route('dosen.grade.edit', $submission->grade);
        }

        return view('grades.create', ['title' => 'Beri Nilai', 'submission' => $submission]);
    }

    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorizeLecturer($request, $submission->assignment->course);

        abort_if($submission->grade, 409, 'Submission ini sudah dinilai.');

        $validated = $this->validateGrade($request, $submission->assignment->max_score);

        $grade = new Grade();
        $grade->submission_id = $submission->id;
        $grade->graded_by = $request->user()->id;
        $grade->score = $validated['score'];
        $grade->feedback = $validated['feedback'] ?? null;
        // graded_at di-set otomatis lewat model event di AppServiceProvider
        $grade->save();

        return redirect()
            ->route('dosen.submissions.show', $submission)
            ->with('success', 'Nilai berhasil disimpan.');
    }

    public function edit(Request $request, Grade $grade): View
    {
        $this->authorizeLecturer($request, $grade->submission->assignment->course);

        return view('grades.edit', ['title' => 'Edit Nilai', 'grade' => $grade]);
    }

    public function update(Request $request, Grade $grade): RedirectResponse
    {
        $this->authorizeLecturer($request, $grade->submission->assignment->course);

        $validated = $this->validateGrade($request, $grade->submission->assignment->max_score);

        $grade->score = $validated['score'];
        $grade->feedback = $validated['feedback'] ?? null;
        $grade->save();

        return redirect()
            ->route('dosen.submissions.show', $grade->submission)
            ->with('success', 'Nilai berhasil diperbarui.');
    }

    // ------- Untuk mahasiswa -------

    public function showForStudent(Request $request, Submission $submission): View
    {
        $this->authorizeStudentSubmission($request, $submission);

        $submission->load('grade');

        return view('grades.student-show', ['title' => 'Nilai Saya', 'submission' => $submission]);
    }

    protected function validateGrade(Request $request, int $maxScore): array
    {
        return $request->validate([
            'score' => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string'],
        ]);
    }

    private function authorizeLecturer(Request $request, Course $course): void
    {
        abort_unless(
            $course->lecturer_id === $request->user()->id,
            403,
            'Kamu bukan dosen pengampu mata kuliah ini.'
        );
    }

    private function authorizeStudentSubmission(Request $request, Submission $submission): void
    {
        $course = $submission->assignment->course;

        abort_unless(
            $course->students()->where('users.id', $request->user()->id)->exists()
                && $submission->user_id === $request->user()->id,
            403,
            'Kamu tidak memiliki izin untuk mengakses nilai ini.'
        );
    }
}
