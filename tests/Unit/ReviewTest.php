<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_reviewer()
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(User::class, $review->reviewer);
        $this->assertEquals($review->reviewer_id, $review->reviewer->id);
    }

    public function test_review_belongs_to_reviewee()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $reviewee = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $reviewee->id,
        ]);

        $this->assertInstanceOf(User::class, $review->reviewee);
        $this->assertEquals($reviewee->id, $review->reviewee->id);
    }

    public function test_review_belongs_to_project()
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(Project::class, $review->project);
        $this->assertEquals($review->project_id, $review->project->id);
    }

    public function test_user_has_reviews_written()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertTrue($user->reviewsWritten->contains($review));
    }

    public function test_user_has_reviews_received()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $reviewee = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $reviewee->id,
        ]);

        $this->assertTrue($reviewee->reviewsReceived->contains($review));
    }

    public function test_project_has_reviews()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $this->assertTrue($project->reviews->contains($review));
    }

    public function test_visible_reviewer_name_returns_anonymous_for_non_executives()
    {
        $organization = Organization::factory()->create();
        $executiveRole = Role::factory()->create(['name' => 'executive']);
        $associateRole = Role::factory()->create(['name' => 'associate']);
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $executiveRole->id,
            'name' => 'John Doe',
        ]);
        
        $viewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $associateRole->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
        ]);

        $this->assertEquals('Anonymous', $review->visibleReviewerName($viewer));
    }

    public function test_visible_reviewer_name_returns_actual_name_for_executives()
    {
        $organization = Organization::factory()->create();
        $executiveRole = Role::factory()->create(['name' => 'executive']);
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $executiveRole->id,
            'name' => 'John Doe',
        ]);
        
        $executiveViewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $executiveRole->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
        ]);

        $this->assertEquals('John Doe', $review->visibleReviewerName($executiveViewer));
    }

    public function test_is_user_review_returns_true_when_reviewee_exists()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $reviewee = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $reviewee->id,
        ]);

        $this->assertTrue($review->isUserReview());
        $this->assertFalse($review->isProjectReview());
    }

    public function test_is_project_review_returns_true_when_no_reviewee()
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create();
        
        $reviewer = User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $review = Review::factory()->create([
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
            'reviewee_user_id' => null,
        ]);

        $this->assertTrue($review->isProjectReview());
        $this->assertFalse($review->isUserReview());
    }
}
