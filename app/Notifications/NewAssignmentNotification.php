<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Assignment $assignment)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $courseName = $this->assignment->course?->name ?? $this->assignment->course?->title ?? '';
        $dueDate = $this->assignment->due_at?->toIso8601String() ?? (string) $this->assignment->due_at;

        return [
            'assignment_id' => $this->assignment->id,
            'id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'judul' => $this->assignment->title,
            'course_name' => $courseName,
            'nama_mata_kuliah' => $courseName,
            'due_date' => $dueDate,
            'due_at' => $dueDate,
            'message' => "Tugas baru: {$this->assignment->title} telah dipublikasikan.",
            'pesan' => "Tugas baru: {$this->assignment->title} telah dipublikasikan.",
            'url' => url('/assignments/' . $this->assignment->id),
        ];
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
