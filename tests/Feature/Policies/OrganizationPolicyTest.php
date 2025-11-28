<?php

namespace Tests\Feature\Policies;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $this->executiveRole = Role::firstOrCreate(['name' => 'executive']);
        $this->managerRole = Role::firstOrCreate(['name' => 'manager']);
        $this->associateRole = Role::firstOrCreate(['name' => 'associate']);
        
        // Create organization
        $this->organization = Organization::factory()->create();
        
        // Create users with different roles
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

    public function test_executive_can_create_organization(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/organizations', ['name' => 'New Org']);

        $response->assertStatus(201);
    }

    public function test_manager_cannot_create_organization(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/organizations', ['name' => 'New Org']);

        $response->assertStatus(403);
    }

    public function test_associate_cannot_create_organization(): void
    {
        $response = $this->actingAs($this->associate)
            ->postJson('/api/v1/organizations', ['name' => 'New Org']);

        $response->assertStatus(403);
    }

    public function test_executive_can_update_organization(): void
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/organizations/{$org->id}", ['name' => 'Updated Org']);

        $response->assertStatus(200);
    }

    public function test_manager_cannot_update_organization(): void
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/organizations/{$org->id}", ['name' => 'Updated Org']);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_organization(): void
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/organizations/{$org->id}");

        $response->assertStatus(204);
    }

    public function test_manager_cannot_delete_organization(): void
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/organizations/{$org->id}");

        $response->assertStatus(403);
    }

    public function test_all_users_can_view_organizations(): void
    {
        $response = $this->actingAs($this->associate)
            ->getJson('/api/v1/organizations');

        $response->assertStatus(200);
    }
}
