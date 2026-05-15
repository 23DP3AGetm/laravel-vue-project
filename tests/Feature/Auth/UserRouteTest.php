<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_route_requires_api_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_user_route_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_blocked_user_cannot_access_authenticated_api_routes(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_BLOCKED,
            'is_blocked' => true,
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user')
            ->assertForbidden()
            ->assertJsonPath('message', 'Account blocked');
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/user/avatar', [
                'avatar' => $file,
            ])
            ->assertOk()
            ->assertJsonStructure(['avatar', 'user']);

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_become_organizator_with_correct_password(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'password' => 'password',
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/become-organizator', [
                'current_password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ORGANIZATOR);
    }

    public function test_user_cannot_become_organizator_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'password' => 'password',
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/become-organizator', [
                'current_password' => 'wrong-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}
