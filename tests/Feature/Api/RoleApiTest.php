<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_roles(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name']
                ]
            ]);
    }

    public function test_can_create_role(): void
    {
        $data = ['name' => 'Test Role'];

        $response = $this->postJson('/api/v1/roles', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Role']);

        $this->assertDatabaseHas('roles', $data);
    }

    public function test_can_show_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->getJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $role->id,
                'name' => $role->name
            ]);
    }

    public function test_can_update_role(): void
    {
        $role = Role::factory()->create();
        $data = ['name' => 'Updated Role'];

        $response = $this->putJson("/api/v1/roles/{$role->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Role']);

        $this->assertDatabaseHas('roles', $data);
    }

    public function test_can_delete_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_validation_fails_when_creating_role_without_name(): void
    {
        $response = $this->postJson('/api/v1/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_fails_when_creating_duplicate_role(): void
    {
        Role::factory()->create(['name' => 'Unique Role']);

        $response = $this->postJson('/api/v1/roles', ['name' => 'Unique Role']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
