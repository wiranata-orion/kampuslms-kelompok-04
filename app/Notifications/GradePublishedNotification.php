<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification
{
    public function __construct(public Grade $grade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $submission = $this->grade->submission;
        $assignment = $submission->assignment;
        $course = $assignment->course;

        return [
            'title' => 'Nilai tugas dipublikasikan',
            'judul' => 'Nilai tugas dipublikasikan',
            'grade_id' => $this->grade->id,
            'message' => "Nilai untuk tugas {$assignment->title} pada mata kuliah {$course->name} telah dipublikasikan.",
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->title,
            'course_id' => $course->id,
            'course_name' => $course->name,
            'url' => url('/submissions/'.$submission->id.'/grade'),
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
