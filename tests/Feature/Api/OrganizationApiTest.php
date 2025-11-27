<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_organizations(): void
    {
        Organization::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/organizations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_can_create_organization(): void
    {
        $data = ['name' => 'Test Organization'];

        $response = $this->postJson('/api/v1/organizations', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Organization']);

        $this->assertDatabaseHas('organizations', $data);
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

    public function test_can_update_organization(): void
    {
        $organization = Organization::factory()->create();
        $data = ['name' => 'Updated Organization'];

        $response = $this->putJson("/api/v1/organizations/{$organization->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Organization']);

        $this->assertDatabaseHas('organizations', $data);
    }

    public function test_can_delete_organization(): void
    {
        $organization = Organization::factory()->create();

        $response = $this->deleteJson("/api/v1/organizations/{$organization->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    public function test_validation_fails_when_creating_organization_without_name(): void
    {
        $response = $this->postJson('/api/v1/organizations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
