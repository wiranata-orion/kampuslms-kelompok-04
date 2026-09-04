<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = [
            ['id' => 1, 'code' => 'SI2514024', 'name' => 'Pemrograman Web', 'sks' => 3, 'lecturer' => 'Dosen A'],
            ['id' => 2, 'code' => 'SI2514010', 'name' => 'Basis Data', 'sks' => 3, 'lecturer' => 'Dosen B'],
        ];

        return view('courses.index', compact('courses'));
    }

    public function show($id)
    {
        $courses = [
            1 => ['id' => 1, 'code' => 'SI2514024', 'name' => 'Pemrograman Web', 'sks' => 3, 'lecturer' => 'Dosen A'],
            2 => ['id' => 2, 'code' => 'SI2514010', 'name' => 'Basis Data', 'sks' => 3, 'lecturer' => 'Dosen B'],
        ];

        $course = $courses[$id] ?? abort(404);

        return view('courses.show', compact('course'));
    }
}