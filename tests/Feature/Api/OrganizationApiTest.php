<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->executiveRole = Role::factory()->create(['name' => 'executive']);
        $this->managerRole = Role::factory()->create(['name' => 'manager']);
        $this->organization = Organization::factory()->create();
        
        $this->executive = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
        ]);
        
        $this->manager = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
        ]);
    }

    public function test_can_list_organizations(): void
    {
        Organization::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/organizations');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_executive_can_create_organization(): void
    {
        $data = ['name' => 'Test Organization'];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/organizations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Organization']);

        $this->assertDatabaseHas('organizations', $data);
    }

    public function test_non_executive_cannot_create_organization(): void
    {
        $data = ['name' => 'Test Organization'];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/organizations', $data);

        $response->assertStatus(403);
    }

    public function test_can_show_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->getJson("/api/v1/organizations/{$organization->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $organization->id,
                'name' => $organization->name
            ]);
    }

    public function test_executive_can_update_organization(): void
    {
        $organization = Organization::factory()->create();
        $data = ['name' => 'Updated Organization'];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/organizations/{$organization->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Organization']);

        $this->assertDatabaseHas('organizations', $data);
    }

    public function test_non_executive_cannot_update_organization(): void
    {
        $organization = Organization::factory()->create();
        $data = ['name' => 'Updated Organization'];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/organizations/{$organization->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/organizations/{$organization->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    public function test_non_executive_cannot_delete_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/organizations/{$organization->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_when_creating_organization_without_name(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/organizations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
