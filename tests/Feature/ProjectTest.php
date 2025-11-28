<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_created(): void
    {
        $organization = Organization::factory()->create();
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Project',
            'description' => 'Test Description',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'organization_id' => $organization->id,
        ]);
    }

    public function test_project_belongs_to_organization(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $project->organization);
        $this->assertEquals($organization->id, $project->organization->id);
    }

    public function test_project_can_have_multiple_teams(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create(['organization_id' => $organization->id]);
        
        $role = Role::inRandomOrder()->first();
        $manager = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $team1 = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
        ]);
        $team2 = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
        ]);

        $project->teams()->attach([$team1->id, $team2->id]);

        $this->assertEquals(2, $project->teams()->count());
        $this->assertTrue($project->teams->contains($team1));
        $this->assertTrue($project->teams->contains($team2));
    }

    public function test_team_can_work_on_multiple_projects(): void
    {
        $organization = Organization::factory()->create();
        
        $role = Role::inRandomOrder()->first();
        $manager = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $team = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
        ]);
        
        $project1 = Project::factory()->create(['organization_id' => $organization->id]);
        $project2 = Project::factory()->create(['organization_id' => $organization->id]);

        $team->projects()->attach([$project1->id, $project2->id]);

        $this->assertEquals(2, $team->projects()->count());
        $this->assertTrue($team->projects->contains($project1));
        $this->assertTrue($team->projects->contains($project2));
    }

    public function test_multiple_teams_can_collaborate_on_project(): void
    {
        $organization = Organization::factory()->create();
        
        $role = Role::inRandomOrder()->first();
        $manager = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $team1 = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
            'name' => 'Frontend Team',
        ]);
        $team2 = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
            'name' => 'Backend Team',
        ]);
        $team3 = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
            'name' => 'DevOps Team',
        ]);

        $project = Project::factory()->create(['organization_id' => $organization->id]);
        
        $project->teams()->attach([$team1->id, $team2->id, $team3->id]);

        $this->assertEquals(3, $project->teams()->count());
        $this->assertTrue($project->teams->contains('name', 'Frontend Team'));
        $this->assertTrue($project->teams->contains('name', 'Backend Team'));
        $this->assertTrue($project->teams->contains('name', 'DevOps Team'));
    }

    public function test_organization_has_projects_relationship(): void
    {
        $organization = Organization::factory()->create();
        
        Project::factory()->count(3)->create(['organization_id' => $organization->id]);

        $this->assertEquals(3, $organization->projects()->count());
    }

    public function test_project_team_pivot_is_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $organization = Organization::factory()->create();
        
        $role = Role::inRandomOrder()->first();
        $manager = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $team = Team::factory()->create([
            'organization_id' => $organization->id,
            'manager_id' => $manager->id,
        ]);
        
        $project = Project::factory()->create(['organization_id' => $organization->id]);

        // First attach should succeed
        $project->teams()->attach($team->id);
        
        // Second attach of same team to same project should fail
        $project->teams()->attach($team->id);
    }
}
