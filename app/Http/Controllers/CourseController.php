<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('lecturer')->get();

        return view('courses.index', [
            'title' => 'Daftar Mata Kuliah',
            'courses' => $courses,
        ]);
    }

    public function show(Course $course)
    {
        $course->load('lecturer');

        return view('courses.show', [
            'title' => 'Detail Mata Kuliah',
            'course' => $course,
        ]);
    }

    public function create()
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', [
            'title' => 'Tambah Mata Kuliah',
            'lecturers' => $lecturers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code',
            'name' => 'required|string',
            'description' => 'required|string',
            'sks' => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,active,archived',
        ]);

        $course = new Course();
        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'];
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', [
            'title' => 'Edit Mata Kuliah',
            'course' => $course,
            'lecturers' => $lecturers,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $course->id,
            'name' => 'required|string',
            'description' => 'required|string',
            'sks' => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,active,archived',
        ]);

        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'];
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}