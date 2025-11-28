<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $organization = \App\Models\Organization::factory()->create();
        $role = \App\Models\Role::factory()->create();
        
        $reviewer = \App\Models\User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);
        
        $project = \App\Models\Project::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        // 50% chance of being a user review vs project review
        $revieweeUserId = fake()->boolean(50) ? \App\Models\User::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ])->id : null;
        
        return [
            'reviewer_id' => $reviewer->id,
            'project_id' => $project->id,
            'reviewee_user_id' => $revieweeUserId,
            'rating' => fake()->numberBetween(1, 5),
            'content' => fake()->paragraph(3),
        ];
    }
}
