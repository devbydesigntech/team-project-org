<?php

namespace Tests\Feature\Policies;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->executiveRole = Role::factory()->create(['name' => 'executive']);
        $this->managerRole = Role::factory()->create(['name' => 'manager']);
        $this->associateRole = Role::factory()->create(['name' => 'associate']);
        
        $this->organization = Organization::factory()->create();
        
        $this->executive = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
        ]);
        
        $this->manager = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
        ]);
        
        $this->associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);
    }

    public function test_executive_can_add_users_to_company(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'organization_id' => $this->organization->id,
                'role_id' => $this->associateRole->id,
            ]);

        $response->assertStatus(201);
    }

    public function test_manager_cannot_add_users(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'organization_id' => $this->organization->id,
                'role_id' => $this->associateRole->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_associate_cannot_add_users(): void
    {
        $response = $this->actingAs($this->associate)
            ->postJson('/api/v1/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'organization_id' => $this->organization->id,
                'role_id' => $this->associateRole->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_update_user_role(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/users/{$user->id}", [
                'role_id' => $this->managerRole->id,
            ]);

        $response->assertStatus(200);
        $this->assertEquals($this->managerRole->id, $user->fresh()->role_id);
    }

    public function test_manager_cannot_update_user_role(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/users/{$user->id}", [
                'role_id' => $this->managerRole->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);
    }

    public function test_manager_cannot_delete_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(403);
    }

    public function test_all_users_can_view_user_list(): void
    {
        $response = $this->actingAs($this->associate)
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }
}
