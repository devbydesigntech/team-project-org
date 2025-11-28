<?php

namespace Tests\Feature\Api;

use App\Models\AdvisoryAssignment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisoryAssignmentApiTest extends TestCase
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

    public function test_can_list_advisory_assignments(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        AdvisoryAssignment::factory()->count(3)->create([
            'project_id' => $project->id,
        ]);

        $response = $this->getJson('/api/v1/advisory-assignments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'project_id', 'starts_at', 'ends_at', 'is_active']
                ]
            ]);
    }

    public function test_executive_can_create_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->addMonths(3)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/advisory-assignments', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'user_id' => $this->manager->id,
                'project_id' => $project->id,
            ]);

        $this->assertDatabaseHas('advisory_assignments', [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_non_executive_cannot_create_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/advisory-assignments', $data);

        $response->assertStatus(403);
    }

    public function test_can_show_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);

        $response = $this->getJson("/api/v1/advisory-assignments/{$assignment->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $assignment->id,
                'user_id' => $this->manager->id,
                'project_id' => $project->id,
            ]);
    }

    public function test_executive_can_update_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
            'starts_at' => now(),
        ]);

        $newEndDate = now()->addMonths(6)->toDateTimeString();
        $data = ['ends_at' => $newEndDate];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/advisory-assignments/{$assignment->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('advisory_assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_non_executive_cannot_update_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);

        $data = ['ends_at' => now()->addMonths(6)->toDateTimeString()];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/advisory-assignments/{$assignment->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/advisory-assignments/{$assignment->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('advisory_assignments', ['id' => $assignment->id]);
    }

    public function test_non_executive_cannot_delete_advisory_assignment(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/advisory-assignments/{$assignment->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/advisory-assignments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'project_id']);
    }

    public function test_validation_fails_with_invalid_end_date(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->subDays(1)->toDateTimeString(), // End before start
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/advisory-assignments', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ends_at']);
    }

    public function test_advisory_assignment_includes_relationships_when_loaded(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $assignment = AdvisoryAssignment::factory()->create([
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);

        $response = $this->getJson("/api/v1/advisory-assignments/{$assignment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'project_id',
                    'advisor',
                    'project',
                    'is_active',
                ]
            ]);
    }

    public function test_can_create_advisory_assignment_without_dates(): void
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/advisory-assignments', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('advisory_assignments', [
            'user_id' => $this->manager->id,
            'project_id' => $project->id,
        ]);
    }
}
