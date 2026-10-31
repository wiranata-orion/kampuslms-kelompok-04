<?php

namespace Tests\Unit;

use App\Listeners\SendAssignmentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use RuntimeException;
use stdClass;

class QueueWorkerConfigTest extends TestCase
{
    public function test_listener_implements_should_queue(): void
    {
        $listener = new SendAssignmentNotification();
        $this->assertInstanceOf(ShouldQueue::class, $listener);
    }

    public function test_listener_has_configured_tries(): void
    {
        $listener = new SendAssignmentNotification();
        $this->assertSame(3, $listener->tries);
    }

    public function test_listener_has_configured_backoff(): void
    {
        $listener = new SendAssignmentNotification();
        $this->assertSame([10, 30, 60], $listener->backoff);
    }

    public function test_listener_implements_failed_method(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return str_contains($message, 'SendAssignmentNotification')
                    && isset($context['event'])
                    && isset($context['message'])
                    && $context['message'] === 'Test failure';
            });

        $listener = new SendAssignmentNotification();
        $event = new stdClass();
        $event->assignment_id = 99;

        $listener->failed($event, new RuntimeException('Test failure'));
    }
}
