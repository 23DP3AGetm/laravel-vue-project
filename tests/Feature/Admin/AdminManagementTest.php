<?php

namespace Tests\Feature\Admin;

use App\Models\AdminAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_endpoint_supports_filters_and_pagination(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $token = $admin->createToken('api-token')->plainTextToken;

        User::factory()->count(11)->create([
            'role' => User::ROLE_USER,
        ]);

        User::factory()->create([
            'name' => 'Organizator Match',
            'email' => 'organizator@example.com',
            'role' => User::ROLE_ORGANIZATOR,
            'status' => User::STATUS_BLOCKED,
            'is_blocked' => true,
        ]);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/admin/users?search=organizator&role=organizator&page=1&per_page=10')
            ->assertOk()
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 1)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonCount(1, 'users')
            ->assertJsonPath('users.0.name', 'Organizator Match')
            ->assertJsonPath('users.0.status', 'blocked');
    }

    public function test_admin_role_changes_are_logged(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $targetUser = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);
        $token = $admin->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/admin/users/{$targetUser->id}/role", [
                'role' => User::ROLE_ORGANIZATOR,
            ])
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_ORGANIZATOR);

        $this->assertDatabaseHas('admin_actions', [
            'admin_id' => $admin->id,
            'action' => AdminAction::ACTION_CHANGE_ROLE,
            'target_user_id' => $targetUser->id,
        ]);
    }

    public function test_admin_user_deletion_is_logged(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $targetUser = User::factory()->create();
        $token = $admin->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/admin/users/{$targetUser->id}")
            ->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);

        $this->assertDatabaseHas('admin_actions', [
            'admin_id' => $admin->id,
            'action' => AdminAction::ACTION_DELETE_USER,
            'target_user_id' => $targetUser->id,
        ]);
    }

    public function test_admin_can_update_user_status(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $targetUser = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
            'is_blocked' => false,
        ]);
        $token = $admin->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/users/{$targetUser->id}/status", [
                'status' => User::STATUS_BLOCKED,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.status', User::STATUS_BLOCKED);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'status' => User::STATUS_BLOCKED,
            'is_blocked' => true,
        ]);
    }

    public function test_admin_cannot_block_self(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $token = $admin->createToken('api-token')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/admin/users/{$admin->id}/status", [
                'status' => User::STATUS_BLOCKED,
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot block yourself');
    }
}
