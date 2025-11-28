<?php

namespace Tests\Feature\Api;

use App\Models\AdvisoryAssignment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Review;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $organization;
    protected $executiveRole;
    protected $managerRole;
    protected $associateRole;
    protected $executive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->executiveRole = Role::firstOrCreate(['name' => 'executive']);
        $this->managerRole = Role::firstOrCreate(['name' => 'manager']);
        $this->associateRole = Role::firstOrCreate(['name' => 'associate']);

        $this->executive = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
        ]);
    }

    public function test_can_create_project_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/reviews', [
                'project_id' => $project->id,
                'rating' => 5,
                'content' => 'Great project!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reviewer_id',
                    'visible_reviewer_name',
                    'project_id',
                    'project',
                    'rating',
                    'content',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'reviewer_id' => $this->executive->id,
                    'project_id' => $project->id,
                    'rating' => 5,
                    'content' => 'Great project!',
                ],
            ]);

        $this->assertDatabaseHas('reviews', [
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
            'reviewee_user_id' => null,
            'rating' => 5,
            'content' => 'Great project!',
        ]);
    }

    public function test_can_create_user_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $reviewee = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/reviews', [
                'project_id' => $project->id,
                'reviewee_user_id' => $reviewee->id,
                'rating' => 4,
                'content' => 'Good work on this project!',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'reviewer_id' => $this->executive->id,
                    'project_id' => $project->id,
                    'reviewee_user_id' => $reviewee->id,
                    'rating' => 4,
                ],
            ]);

        $this->assertDatabaseHas('reviews', [
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $reviewee->id,
            'rating' => 4,
        ]);
    }

    public function test_executive_sees_all_reviews()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $user1 = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $user2 = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $review1 = Review::factory()->create([
            'reviewer_id' => $user1->id,
            'project_id' => $project->id,
        ]);

        $review2 = Review::factory()->create([
            'reviewer_id' => $user2->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_executive_sees_actual_reviewer_name()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $reviewer = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'John Reviewer',
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->getJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'visible_reviewer_name' => 'John Reviewer',
                ],
            ]);
    }

    public function test_non_executive_sees_anonymous_reviewer()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $reviewer = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'John Reviewer',
        ]);

        $associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->executive->id,
        ]);

        $team->members()->attach($associate->id);
        $team->projects()->attach($project->id);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($associate)
            ->getJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'visible_reviewer_name' => 'Anonymous',
                ],
            ]);
    }

    public function test_associate_sees_team_project_reviews()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->executive->id,
        ]);

        $team->members()->attach($associate->id);
        $team->projects()->attach($project->id);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($associate)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_associate_sees_reviews_about_themselves()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $associate->id,
        ]);

        $response = $this->actingAs($associate)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_manager_sees_team_member_reviews()
    {
        $manager = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
        ]);

        $associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $manager->id,
        ]);

        $team->members()->attach($associate->id);

        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $associate->id,
        ]);

        $response = $this->actingAs($manager)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_manager_sees_team_project_reviews()
    {
        $manager = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'manager_id' => $manager->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team->projects()->attach($project->id);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($manager)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_advisor_sees_advised_project_reviews()
    {
        $advisor = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        AdvisoryAssignment::factory()->create([
            'user_id' => $advisor->id,
            'project_id' => $project->id,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5),
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($advisor)
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_update_own_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
            'rating' => 3,
            'content' => 'Original content',
        ]);

        $response = $this->actingAs($this->executive)
            ->putJson('/api/v1/reviews/' . $review->id, [
                'rating' => 5,
                'content' => 'Updated content',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'rating' => 5,
                    'content' => 'Updated content',
                ],
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'content' => 'Updated content',
        ]);
    }

    public function test_user_cannot_update_others_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $otherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $otherUser->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->putJson('/api/v1/reviews/' . $review->id, [
                'rating' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $this->executive->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_executive_can_delete_any_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $associate = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $associate->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_non_executive_cannot_delete_others_review()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $associate1 = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $associate2 = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $associate1->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($associate2)
            ->deleteJson('/api/v1/reviews/' . $review->id);

        $response->assertStatus(403);
    }

    public function test_validation_requires_project_id()
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/reviews', [
                'rating' => 5,
                'content' => 'Great!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_validation_requires_rating()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/reviews', [
                'project_id' => $project->id,
                'content' => 'Great!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_validation_rating_must_be_between_1_and_5()
    {
        $project = Project::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/reviews', [
                'project_id' => $project->id,
                'rating' => 10,
                'content' => 'Great!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }
}
