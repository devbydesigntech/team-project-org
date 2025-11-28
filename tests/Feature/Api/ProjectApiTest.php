<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->executiveRole = Role::firstOrCreate(['name' => 'executive']);
        $this->managerRole = Role::firstOrCreate(['name' => 'manager']);
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

    public function test_can_list_projects(): void
    {
        Project::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->manager)->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'organization_id', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_executive_can_create_project(): void
    {
        $data = [
            'name' => 'New Platform',
            'description' => 'A new customer platform',
            'organization_id' => $this->organization->id,
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/projects', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'New Platform',
                'description' => 'A new customer platform',
            ]);

        $this->assertDatabaseHas('projects', $data);
    }

    public function test_non_executive_cannot_create_project(): void
    {
        $data = [
            'name' => 'New Platform',
            'description' => 'A new customer platform',
            'organization_id' => $this->organization->id,
        ];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/projects', $data);

        $response->assertStatus(403);
    }

    public function test_can_show_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->manager)->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $project->id,
                'name' => $project->name,
            ]);
    }

    public function test_executive_can_update_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = ['name' => 'Updated Project Name'];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/projects/{$project->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Project Name']);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
        ]);
    }

    public function test_non_executive_cannot_update_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = ['name' => 'Updated Project Name'];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/projects/{$project->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_non_executive_cannot_delete_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'organization_id']);
    }

    public function test_executive_can_assign_team_to_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson("/api/v1/projects/{$project->id}/teams", [
                'team_id' => $team->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_non_executive_cannot_assign_team_to_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/v1/projects/{$project->id}/teams", [
                'team_id' => $team->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_executive_can_remove_team_from_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $project->teams()->attach($team->id);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/projects/{$project->id}/teams/{$team->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_non_executive_cannot_remove_team_from_project(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $project->teams()->attach($team->id);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/projects/{$project->id}/teams/{$team->id}");

        $response->assertStatus(403);
    }

    public function test_project_includes_relationships_when_loaded(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
        ]);

        $project->teams()->attach($team->id);

        $response = $this->actingAs($this->manager)->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'organization',
                    'teams',
                ]
            ]);
    }
}
