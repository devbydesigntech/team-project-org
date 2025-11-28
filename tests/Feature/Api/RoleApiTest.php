<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleApiTest extends TestCase
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

    public function test_can_list_roles(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->actingAs($this->manager)->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name']
                ]
            ]);
    }

    public function test_executive_can_create_role(): void
    {
        $data = ['name' => 'Test Role'];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/roles', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Role']);

        $this->assertDatabaseHas('roles', $data);
    }

    public function test_non_executive_cannot_create_role(): void
    {
        $data = ['name' => 'Test Role'];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/roles', $data);

        $response->assertStatus(403);
    }

    public function test_can_show_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->manager)->getJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $role->id,
                'name' => $role->name
            ]);
    }

    public function test_executive_can_update_role(): void
    {
        $role = Role::factory()->create();
        $data = ['name' => 'Updated Role'];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/roles/{$role->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Role']);

        $this->assertDatabaseHas('roles', $data);
    }

    public function test_non_executive_cannot_update_role(): void
    {
        $role = Role::factory()->create();
        $data = ['name' => 'Updated Role'];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/roles/{$role->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_non_executive_cannot_delete_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_when_creating_role_without_name(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_when_creating_duplicate_role(): void
    {
        Role::factory()->create(['name' => 'Unique Role']);

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/roles', ['name' => 'Unique Role']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
