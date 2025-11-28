<?php

namespace Tests\Feature\Policies;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePolicyTest extends TestCase
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

    public function test_executive_can_create_role(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/roles', ['name' => 'Director']);

        $response->assertStatus(201);
    }

    public function test_manager_cannot_create_role(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/roles', ['name' => 'Director']);

        $response->assertStatus(403);
    }

    public function test_associate_cannot_create_role(): void
    {
        $response = $this->actingAs($this->associate)
            ->postJson('/api/v1/roles', ['name' => 'Director']);

        $response->assertStatus(403);
    }

    public function test_executive_can_update_role(): void
    {
        $role = Role::factory()->create(['name' => 'Analyst']);

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/roles/{$role->id}", ['name' => 'Senior Analyst']);

        $response->assertStatus(200);
    }

    public function test_manager_cannot_update_role(): void
    {
        $role = Role::factory()->create(['name' => 'Analyst']);

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/roles/{$role->id}", ['name' => 'Senior Analyst']);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_role(): void
    {
        $role = Role::factory()->create(['name' => 'Analyst']);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);
    }

    public function test_manager_cannot_delete_role(): void
    {
        $role = Role::factory()->create(['name' => 'Analyst']);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(403);
    }

    public function test_all_users_can_view_roles(): void
    {
        $response = $this->actingAs($this->associate)
            ->getJson('/api/v1/roles');

        $response->assertStatus(200);
    }
}
