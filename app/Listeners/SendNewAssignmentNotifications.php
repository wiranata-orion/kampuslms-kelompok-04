<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendNewAssignmentNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Jumlah percobaan maksimal sebelum job dinyatakan gagal.
     */
    public int $tries = 3;

    /**
     * Waktu tunda (detik) antar percobaan ulang.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(AssignmentPublished $event): void
    {
        $event->assignment->loadMissing('course.students');

        $students = $event->assignment->course->students;

        if ($students && $students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($event->assignment));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(mixed $event, Throwable $exception): void
    {
        $assignmentId = null;
        if (is_object($event) && isset($event->assignment)) {
            $assignmentId = $event->assignment->id ?? null;
        }

        Log::error('SendNewAssignmentNotifications job gagal diproses setelah percobaan maksimal.', [
            'assignment_id' => $assignmentId,
            'event' => is_object($event) ? get_class($event) : gettype($event),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
