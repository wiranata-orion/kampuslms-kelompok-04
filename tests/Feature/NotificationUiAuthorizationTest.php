<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationUiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_own_notifications_list(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Notifikasi untuk user
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Tugas Matematika', 'message' => 'Tugas baru dipublikasikan']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notifikasi untuk user lain
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode(['title' => 'Tugas Fisika', 'message' => 'Tugas rahasia']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.data.title', 'Tugas Matematika');
    }

    public function test_unread_count_endpoint_returns_accurate_count(): void
    {
        $user = User::factory()->create();

        // 2 notifikasi belum dibaca, 1 sudah dibaca
        DB::table('notifications')->insert([
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\NewAssignmentNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'Tugas 1']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\NewAssignmentNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'Tugas 2']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\NewAssignmentNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'Tugas 3']),
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications/unread-count');

        $response->assertOk();
        $response->assertJsonPath('data.unread_count', 2);
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notifId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Tugas Baru']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/notifications/{$notifId}/read");

        $response->assertOk();
        $response->assertJsonPath('data.id', $notifId);
        $this->assertNotNull(DB::table('notifications')->where('id', $notifId)->value('read_at'));
    }

    public function test_user_cannot_mark_other_users_notification_returns_403_forbidden(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $notifId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => json_encode(['title' => 'Notifikasi Rahasia']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attacker mencoba menandai notifikasi milik owner
        $response = $this->actingAs($attacker, 'sanctum')->postJson("/api/v1/notifications/{$notifId}/read");

        $response->assertForbidden();
        // Pastikan status read_at tidak berubah di database
        $this->assertNull(DB::table('notifications')->where('id', $notifId)->value('read_at'));
    }

    public function test_user_cannot_view_other_users_single_notification_returns_403_forbidden(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $notifId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notifId,
            'type' => 'App\\Notifications\\NewAssignmentNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'data' => json_encode(['title' => 'Notifikasi Pribadi']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Attacker mencoba melihat detail notifikasi milik owner
        $response = $this->actingAs($attacker, 'sanctum')->getJson("/api/v1/notifications/{$notifId}");

        $response->assertForbidden();
    }

    public function test_user_can_mark_all_unread_notifications_as_read(): void
    {
        $user = User::factory()->create();

        DB::table('notifications')->insert([
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\NewAssignmentNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'A']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\NewAssignmentNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['title' => 'B']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertSame(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/notifications/read-all');

        $response->assertOk();
        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}
