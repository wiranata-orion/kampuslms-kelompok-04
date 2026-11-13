<?php

namespace App\Services;

use App\Models\CourseSession;
use RuntimeException;

class AttendanceCodeService
{
    public function forSession(CourseSession $session): string
    {
        $key = (string) config('app.key');
        if ($key === '') {
            throw new RuntimeException('APP_KEY harus dikonfigurasi untuk membuat kode presensi.');
        }

        $payload = implode('|', [
            $session->id,
            $session->starts_at->timestamp,
            $session->ends_at->timestamp,
        ]);

        return strtoupper(substr(hash_hmac('sha256', $payload, $key), 0, 5));
    }

    public function matches(CourseSession $session, string $code): bool
    {
        return hash_equals($this->forSession($session), strtoupper(trim($code)));
    }
}
