<?php

namespace Tests\Feature\Policies;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->executiveRole = Role::firstOrCreate(['name' => 'executive']);
        $this->managerRole = Role::firstOrCreate(['name' => 'manager']);
        $this->associateRole = Role::firstOrCreate(['name' => 'associate']);
        
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

    public function test_executive_can_create_team(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/teams', [
                'name' => 'New Team',
                'organization_id' => $this->organization->id,
                'manager_id' => $this->manager->id,
            ]);

        $response->assertStatus(201);
    }

    public function test_manager_cannot_create_team(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/teams', [
                'name' => 'New Team',
                'organization_id' => $this->organization->id,
                'manager_id' => $this->manager->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_associate_cannot_create_team(): void
    {
        $response = $this->actingAs($this->associate)
            ->postJson('/api/v1/teams', [
                'name' => 'New Team',
                'organization_id' => $this->organization->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_update_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/teams/{$team->id}", [
                'name' => 'Updated Team',
            ]);

        $response->assertStatus(200);
    }

    public function test_manager_cannot_update_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/teams/{$team->id}", [
                'name' => 'Updated Team',
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(204);
    }

    public function test_manager_cannot_delete_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/teams/{$team->id}");

        $response->assertStatus(403);
    }

    public function test_executive_can_assign_users_to_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson("/api/v1/teams/{$team->id}/members", [
                'user_id' => $this->associate->id,
                'team_role' => 'Developer',
            ]);

        $response->assertStatus(200);
    }

    public function test_manager_cannot_assign_users_to_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/v1/teams/{$team->id}/members", [
                'user_id' => $this->associate->id,
                'team_role' => 'Developer',
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_remove_users_from_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);
        
        $team->members()->attach($this->associate->id, ['team_role' => 'Developer']);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/teams/{$team->id}/members/{$this->associate->id}");

        $response->assertStatus(200);
    }

    public function test_manager_cannot_remove_users_from_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);
        
        $team->members()->attach($this->associate->id, ['team_role' => 'Developer']);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/teams/{$team->id}/members/{$this->associate->id}");

        $response->assertStatus(403);
    }

    public function test_all_users_can_view_teams(): void
    {
        $response = $this->actingAs($this->associate)
            ->getJson('/api/v1/teams');

        $response->assertStatus(200);
    }
}
