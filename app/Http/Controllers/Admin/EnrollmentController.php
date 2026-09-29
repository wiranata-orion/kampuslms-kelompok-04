<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Course $course)
    {
        $students = $course->students()->orderBy('name')->paginate(20);

        return view('admin.enrollments.index', ['title' => 'Peserta Mata Kuliah', 'course' => $course, 'students' => $students]);
    }

    public function create(Course $course)
    {
        $enrolledIds = $course->students()->pluck('users.id');

        $candidates = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('admin.enrollments.create', ['title' => 'Tambah Peserta', 'course' => $course, 'candidates' => $candidates]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::where('id', $validated['user_id'])->where('role', 'mahasiswa')->firstOrFail();

        if ($course->students()->where('users.id', $student->id)->exists()) {
            return back()->withErrors(['user_id' => 'Mahasiswa ini sudah terdaftar di mata kuliah ini.']);
        }

        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        return redirect()
            ->route('admin.courses.enrollments.index', $course)
            ->with('success', 'Mahasiswa berhasil didaftarkan.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $courseId = $enrollment->course_id;
        $enrollment->delete();

        return redirect()
            ->route('admin.courses.enrollments.index', $courseId)
            ->with('success', 'Mahasiswa berhasil dikeluarkan dari mata kuliah.');
    }
}