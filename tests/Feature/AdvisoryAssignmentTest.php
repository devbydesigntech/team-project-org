<?php

namespace Tests\Feature;

use App\Models\AdvisoryAssignment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisoryAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisory_assignment_can_be_created(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['name' => 'manager']);
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertDatabaseHas('advisory_assignments', [
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_advisory_assignment_belongs_to_user(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['name' => 'associate']);
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(User::class, $assignment->user);
        $this->assertInstanceOf(User::class, $assignment->advisor);
        $this->assertEquals($user->id, $assignment->user->id);
        $this->assertEquals($user->id, $assignment->advisor->id);
    }

    public function test_advisory_assignment_belongs_to_project(): void
    {
        $organization = Organization::factory()->create();
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(Project::class, $assignment->project);
        $this->assertEquals($project->id, $assignment->project->id);
    }

    public function test_user_can_have_multiple_advisory_assignments(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['name' => 'manager']);
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        $project1 = Project::factory()->create(['organization_id' => $organization->id]);
        $project2 = Project::factory()->create(['organization_id' => $organization->id]);

        AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
        ]);
        AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project2->id,
        ]);

        $this->assertEquals(2, $user->advisoryAssignments()->count());
        $this->assertEquals(2, $user->advisoryProjects()->count());
    }

    public function test_project_can_have_multiple_advisors(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['name' => 'associate']);
        
        $project = Project::factory()->create(['organization_id' => $organization->id]);

        $user1 = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        $user2 = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        AdvisoryAssignment::factory()->create([
            'user_id' => $user1->id,
            'project_id' => $project->id,
        ]);
        AdvisoryAssignment::factory()->create([
            'user_id' => $user2->id,
            'project_id' => $project->id,
        ]);

        $this->assertEquals(2, $project->advisoryAssignments()->count());
        $this->assertEquals(2, $project->advisors()->count());
    }

    public function test_advisory_assignment_is_active_when_within_date_range(): void
    {
        $assignment = AdvisoryAssignment::factory()->create([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5),
        ]);

        $this->assertTrue($assignment->isActive());
    }

    public function test_advisory_assignment_is_not_active_when_before_start_date(): void
    {
        $assignment = AdvisoryAssignment::factory()->create([
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(10),
        ]);

        $this->assertFalse($assignment->isActive());
    }

    public function test_advisory_assignment_is_not_active_when_after_end_date(): void
    {
        $assignment = AdvisoryAssignment::factory()->create([
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(5),
        ]);

        $this->assertFalse($assignment->isActive());
    }

    public function test_advisory_assignment_is_active_with_null_dates(): void
    {
        $assignment = AdvisoryAssignment::factory()->create([
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $this->assertTrue($assignment->isActive());
    }

    public function test_advisory_assignment_user_project_is_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['name' => 'manager']);
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        // First assignment should succeed
        AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        // Second assignment with same user and project should fail
        AdvisoryAssignment::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);
    }
}
