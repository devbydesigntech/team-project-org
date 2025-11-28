<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdvisoryAssignment>
 */
class AdvisoryAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-6 months', 'now');
        $endsAt = fake()->optional(0.7)->dateTimeBetween($startsAt, '+3 months');
        
        $organization = \App\Models\Organization::factory()->create();
        $role = \App\Models\Role::factory()->create();
        
        return [
            'user_id' => \App\Models\User::factory()->create([
                'organization_id' => $organization->id,
                'role_id' => $role->id,
            ]),
            'project_id' => \App\Models\Project::factory()->create([
                'organization_id' => $organization->id,
            ]),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }
}
