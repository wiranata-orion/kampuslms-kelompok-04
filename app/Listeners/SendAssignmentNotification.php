<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Jumlah percobaan maksimal sebelum job dinyatakan gagal.
     */
    public int $tries = 3;

    /**
     * Waktu tunda (detik) antar percobaan ulang sebelum job dicoba kembali.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
    }

    /**
     * Handle a job failure.
     */
    public function failed(object $event, Throwable $exception): void
    {
        Log::error('Job antrean pada SendAssignmentNotification gagal setelah percobaan maksimal.', [
            'event' => is_object($event) ? get_class($event) : gettype($event),
            'message' => $exception->getMessage(),
            'exception' => $exception,
        ]);
    }
}
