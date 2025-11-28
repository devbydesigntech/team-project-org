<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamApiTest extends TestCase
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

    public function test_can_list_teams(): void
    {
        Team::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $response = $this->actingAs($this->manager)->getJson('/api/v1/teams');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'organization_id', 'manager_id']
                ]
            ]);
    }

    public function test_executive_can_create_team(): void
    {
        $data = [
            'name' => 'Test Team',
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/teams', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Team']);

        $this->assertDatabaseHas('teams', $data);
    }

    public function test_non_executive_cannot_create_team(): void
    {
        $data = [
            'name' => 'Test Team',
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/teams', $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_create_team_without_manager(): void
    {
        $data = [
            'name' => 'Test Team',
            'organization_id' => $this->organization->id
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/teams', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Team']);

        $this->assertDatabaseHas('teams', ['name' => 'Test Team', 'manager_id' => null]);
    }

    public function test_can_show_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $response = $this->actingAs($this->manager)->getJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $team->id,
                'name' => $team->name
            ]);
    }

    public function test_executive_can_update_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $data = ['name' => 'Updated Team'];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/teams/{$team->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Team']);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Team'
        ]);
    }

    public function test_non_executive_cannot_update_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $data = ['name' => 'Updated Team'];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/teams/{$team->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_non_executive_cannot_delete_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/teams', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'organization_id']);
    }

    public function test_executive_can_add_member_to_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id
        ]);

        $data = [
            'user_id' => $user->id,
            'team_role' => 'Developer'
        ];

        $response = $this->actingAs($this->executive)
            ->postJson("/api/v1/teams/{$team->id}/members", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'team_role' => 'Developer'
        ]);
    }

    public function test_non_executive_cannot_add_member_to_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id
        ]);

        $data = [
            'user_id' => $user->id,
            'team_role' => 'Developer'
        ];

        $response = $this->actingAs($this->manager)
            ->postJson("/api/v1/teams/{$team->id}/members", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_remove_member_from_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id
        ]);

        $team->members()->attach($user->id, ['team_role' => 'Developer']);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/teams/{$team->id}/members/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('team_members', [
            'team_id' => $team->id,
            'user_id' => $user->id
        ]);
    }

    public function test_non_executive_cannot_remove_member_from_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id
        ]);

        $team->members()->attach($user->id, ['team_role' => 'Developer']);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/teams/{$team->id}/members/{$user->id}");

        $response->assertStatus(403);
    }

    public function test_team_includes_relationships_when_requested(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id
        ]);

        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id
        ]);

        $team->members()->attach($user->id);

        $response = $this->actingAs($this->manager)->getJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'organization',
                    'manager',
                    'members'
                ]
            ]);
    }
}
