<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = match ($user->role) {
            'admin' => [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
            ],
            'dosen' => [
                'total_courses' => $user->taughtCourses()->count(),
                'total_assignments' => Assignment::whereIn(
                    'course_id',
                    $user->taughtCourses()->pluck('courses.id')
                )->count(),
            ],
            'mahasiswa' => [
                'total_courses' => $user->courses()->count(),
                'total_submissions' => $user->submissions()->count(),
            ],
            default => [],
        };

        // CATATAN: proyek ini sebelumnya memakai about.blade.php sebagai
        // halaman dashboard (Route::view('/dashboard', 'about')). Rename
        // file itu jadi resources/views/dashboard.blade.php, atau ubah
        // 'dashboard' di bawah ini jadi 'about' kalau belum sempat
        // di-rename.
        return view('dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
        ]);
    }
}