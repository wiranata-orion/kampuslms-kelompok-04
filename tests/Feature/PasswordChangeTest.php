<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertUnauthorized();
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama_123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'salah_password',
                'password' => 'password_baru_123',
                'password_confirmation' => 'password_baru_123',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_requires_confirmation_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama_123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'password_lama_123',
                'password' => 'password_baru_123',
                'password_confirmation' => 'tidak_cocok_123',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_change_password_requires_minimum_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama_123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'password_lama_123',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_successfully_change_password_and_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'pengguna@kampus.test',
            'password' => Hash::make('password_lama_123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'password_lama_123',
                'password' => 'password_baru_123',
                'password_confirmation' => 'password_baru_123',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru_123', $user->password));

        // Test login with the new password
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'pengguna@kampus.test',
            'password' => 'password_baru_123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }
}
