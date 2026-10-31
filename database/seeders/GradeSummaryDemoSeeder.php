<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class GradeSummaryDemoSeeder extends Seeder
{
    private const COURSE_CODE = 'REKAP-DEMO-01';

    public function run(): void
    {
        DB::transaction(function (): void {
            $lecturer = User::query()
                ->where('email', 'dosen@kampuslms.test')
                ->where('role', 'dosen')
                ->first();
            $student = User::query()
                ->where('email', 'mahasiswa@kampuslms.test')
                ->where('role', 'mahasiswa')
                ->first();

            if (! $lecturer || ! $student) {
                throw new LogicException('Akun demo dosen dan mahasiswa harus tersedia. Jalankan DemoAccountSeeder terlebih dahulu.');
            }

            $course = Course::where('code', self::COURSE_CODE)->first();

            if (! $course) {
                $course = new Course;
                $course->code = self::COURSE_CODE;
                $course->name = 'Mata Kuliah Demo Rekap Nilai';
                $course->description = 'Data demo untuk pengujian rekap nilai.';
                $course->sks = 3;
                $course->lecturer_id = $lecturer->id;
                $course->status = 'active';
                $course->save();
            }

            if ((int) $course->lecturer_id !== (int) $lecturer->id || $course->status !== 'active') {
                throw new LogicException('Course REKAP-DEMO-01 sudah ada tetapi tidak sesuai. Data yang ada tidak diubah.');
            }

            if (! $course->students()->whereKey($student->id)->exists()) {
                $course->students()->attach($student->id, [
                    'enrolled_at' => now()->subDays(30),
                ]);
            }

            $fixtures = [
                ['title' => 'Rekap Demo - Nilai Publikasi 1', 'score' => 84, 'published' => true],
                ['title' => 'Rekap Demo - Nilai Publikasi 2', 'score' => 93, 'published' => true],
                ['title' => 'Rekap Demo - Nilai Belum Dipublikasikan', 'score' => 70, 'published' => false],
                ['title' => 'Rekap Demo - Submission Belum Dinilai', 'score' => null, 'published' => false],
            ];

            foreach ($fixtures as $index => $fixture) {
                $assignment = Assignment::where('course_id', $course->id)
                    ->where('title', $fixture['title'])
                    ->first();

                if (! $assignment) {
                    $assignment = new Assignment;
                    $assignment->course_id = $course->id;
                    $assignment->created_by = $lecturer->id;
                    $assignment->title = $fixture['title'];
                    $assignment->instructions = 'Data demo untuk pengujian rekap nilai.';
                    $assignment->due_at = now()->subDays(14 - $index);
                    $assignment->max_score = 100;
                    $assignment->allow_late = true;
                    $assignment->status = 'published';
                    $assignment->save();
                } elseif ((int) $assignment->created_by !== (int) $lecturer->id || $assignment->status !== 'published') {
                    throw new LogicException("Tugas {$fixture['title']} sudah ada tetapi tidak sesuai. Data yang ada tidak diubah.");
                }

                $submission = Submission::firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'file_path' => "demo/grade-summary/assignment-{$assignment->id}-student-{$student->id}.pdf",
                        'original_name' => 'jawaban-demo.pdf',
                        'file_size' => 1024,
                        'note' => 'Submission demo untuk pengujian rekap nilai.',
                        'submitted_at' => now()->subDays(10 - $index),
                        'is_late' => false,
                    ],
                );

                if ($fixture['score'] === null) {
                    continue;
                }

                $grade = Grade::firstOrCreate(
                    ['submission_id' => $submission->id],
                    [
                        'graded_by' => $lecturer->id,
                        'score' => $fixture['score'],
                        'feedback' => $fixture['published']
                            ? 'Feedback nilai demo yang telah dipublikasikan.'
                            : 'Feedback nilai demo yang belum dipublikasikan.',
                        'graded_at' => now()->subDays(3 - $index),
                    ],
                );

                if ($grade->wasRecentlyCreated && $fixture['published']) {
                    $grade->published_at = now()->subDays(2 - $index);
                    $grade->save();
                }

                if ((float) $grade->score !== (float) $fixture['score']
                    || ($grade->published_at !== null) !== $fixture['published']) {
                    throw new LogicException("Nilai demo untuk tugas {$fixture['title']} sudah ada dengan data berbeda. Data yang ada tidak diubah.");
                }
            }
        });
    }
}
