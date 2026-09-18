<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');

        $courses = Course::query()
            ->with('lecturer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['draft', 'active', 'archived'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('courses.index', [
            'title' => 'Daftar Mata Kuliah',
            'courses' => $courses,
            'search' => $search,
            'status' => $status,
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