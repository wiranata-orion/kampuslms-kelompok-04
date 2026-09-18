<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan Seeder Akun Demo Wajib
        $this->call(DemoAccountSeeder::class);

        // 2. Generate Pengguna Tambahan (Total minimal: 1 Admin, 3 Dosen, 30 Mahasiswa)
        $dosens = User::factory()->dosen()->count(2)->create(); // +1 dari akun demo
        $demoDosen = User::where('email', 'dosen@kampuslms.test')->first();
        $allDosens = $dosens->concat([$demoDosen]);

        $mahasiswas = User::factory()->count(29)->create(); // +1 dari akun demo
        $demoMahasiswa = User::where('email', 'mahasiswa@kampuslms.test')->first();
        $allMahasiswas = $mahasiswas->concat([$demoMahasiswa]);

        // 3. Generate 5 Mata Kuliah
        $courses = Course::factory()->count(5)->create([
            'status' => 'active',
            'lecturer_id' => fn () => $allDosens->random()->id,
        ]);

        // 4. Enroll Mahasiswa (Tiap MK minimal 15 Mahasiswa)
        foreach ($courses as $course) {
            // Pastikan akun demo mahasiswa terdaftar di semua MK untuk kemudahan pengujian
            $assignedStudents = $allMahasiswas->random(15);
            if (! $assignedStudents->contains($demoMahasiswa)) {
                $assignedStudents->push($demoMahasiswa);
            }

            foreach ($assignedStudents as $student) {
                $course->students()->attach($student->id, [
                    'enrolled_at' => now()->subDays(rand(10, 30)),
                ]);
            }

            // Generate Materi untuk tiap MK
            Material::factory()->count(3)->create([
                'course_id' => $course->id,
                'uploaded_by' => $course->lecturer_id,
            ]);

            // 5. Generate 3 Tugas per MK (Sudah Lewat, Aktif, dan Draft)
            $assignments = [
                // Tugas 1: Sudah lewat deadline
                Assignment::factory()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                    'status' => 'published',
                    'due_at' => now()->subDays(rand(2, 10)),
                ]),
                // Tugas 2: Masih aktif
                Assignment::factory()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                    'status' => 'published',
                    'due_at' => now()->addDays(rand(3, 14)),
                ]),
                // Tugas 3: Draft
                Assignment::factory()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                    'status' => 'draft',
                    'due_at' => now()->addDays(20),
                ]),
            ];

            // 6. Generate Submission (Hanya untuk Tugas yang 'published')
            $publishedAssignments = array_filter($assignments, fn ($a) => $a->status === 'published');
            
            foreach ($publishedAssignments as $assignment) {
                $enrolledStudents = $course->students;

                foreach ($enrolledStudents as $student) {
                    // Buat submission untuk sebagian besar mahasiswa
                    if (rand(1, 10) <= 8) {
                        Submission::factory()->create([
                            'assignment_id' => $assignment->id,
                            'user_id' => $student->id,
                            'submitted_at' => $assignment->due_at->subHours(rand(1, 48)),
                        ]);
                    }
                }
            }
        }

        // 7. Penilaian Submissions (>= 100 submission, ~60% dinilai)
        $submissions = Submission::all();
        $targetGradedCount = (int) ($submissions->count() * 0.6);

        $submissionsToGrade = $submissions->random(min($targetGradedCount, $submissions->count()));

        foreach ($submissionsToGrade as $submission) {
            Grade::factory()->create([
                'submission_id' => $submission->id,
                'graded_by' => $submission->assignment->course->lecturer_id,
            ]);
        }
    }
}