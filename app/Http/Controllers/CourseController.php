<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * courses.index (grup Umum) — katalog mata kuliah, semua peran.
     */
    public function index(Request $request): View
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

    /**
     * courses.show (grup Umum) — admin & dosen bebas lihat semua course;
     * mahasiswa HANYA boleh lihat course yang dia ikuti (kesepakatan poin 2).
     */
    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        if ($user->role === 'mahasiswa') {
            abort_unless(
                $course->students()->where('users.id', $user->id)->exists(),
                403,
                'Kamu tidak terdaftar di mata kuliah ini.'
            );
        }

        $course->load('lecturer');

        return view('courses.show', [
            'title' => 'Detail Mata Kuliah',
            'course' => $course,
        ]);
    }

    /**
     * dosen.courses.index & mahasiswa.courses.index — "mata kuliah saya".
     *
     * CATATAN: sengaja pakai view 'courses.my' yang TERPISAH dari
     * 'courses.index', karena view admin punya tombol Edit/Hapus yang
     * tidak boleh tampil untuk dosen/mahasiswa. View ini perlu dibuat
     * oleh rekan front-end.
     */
    public function myCourses(Request $request): View
    {
        $user = $request->user();

        $courses = match ($user->role) {
            'dosen' => $user->taughtCourses()->with('lecturer')->orderBy('name')->paginate(15),
            'mahasiswa' => $user->courses()->with('lecturer')->orderBy('name')->paginate(15),
            default => abort(403),
        };

        return view('courses.my', [
            'title' => 'Mata Kuliah Saya',
            'courses' => $courses,
        ]);
    }

    /**
     * admin.courses.create
     */
    public function create(): View
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.create', [
            'title' => 'Tambah Mata Kuliah',
            'lecturers' => $lecturers,
        ]);
    }

    /**
     * admin.courses.store
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $course = new Course();
        $course->code = $validated['code'];
        $course->name = $validated['name'];
        // StoreCourseRequest menandai 'description' nullable, tapi kolom
        // ini NOT NULL di migrasi — fallback ke string kosong supaya
        // tidak gagal di level database.
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * admin.courses.edit
     */
    public function edit(Course $course): View
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.edit', [
            'title' => 'Edit Mata Kuliah',
            'course' => $course,
            'lecturers' => $lecturers,
        ]);
    }

    /**
     * admin.courses.update
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $validated = $request->validated();

        $course->code = $validated['code'];
        $course->name = $validated['name'];
        $course->description = $validated['description'] ?? '';
        $course->sks = $validated['sks'];
        $course->lecturer_id = $validated['lecturer_id'];
        $course->status = $validated['status'];
        $course->save();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    /**
     * admin.courses.destroy
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }
}