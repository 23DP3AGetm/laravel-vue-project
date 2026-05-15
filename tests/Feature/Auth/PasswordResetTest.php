<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this
            ->postJson('/api/forgot-password', [
                'email' => $user->email,
            ])
            ->assertOk()
            ->assertJsonStructure(['message', 'status']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this
            ->postJson('/api/reset-password', [
                'token' => $token,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertOk()
            ->assertJsonStructure(['message', 'status']);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
