<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_organization_can_be_created(): void
    {
        $organization = Organization::create([
            'name' => 'Test Organization',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
        ]);
    }

    public function test_organization_has_users_relationship(): void
    {
        $organization = Organization::create(['name' => 'Test Org']);
        $role = Role::where('name', 'executive')->first();

        $user = User::create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($organization->users->contains($user));
        $this->assertEquals($organization->id, $user->organization_id);
    }

    public function test_organization_has_teams_relationship(): void
    {
        $organization = Organization::create(['name' => 'Test Org']);

        $team = Team::create([
            'organization_id' => $organization->id,
            'name' => 'Test Team',
        ]);

        $this->assertTrue($organization->teams->contains($team));
        $this->assertEquals($organization->id, $team->organization_id);
    }

    public function test_organization_can_have_multiple_users(): void
    {
        $organization = Organization::create(['name' => 'Test Org']);
        $role = Role::where('name', 'associate')->first();

        User::create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertCount(2, $organization->fresh()->users);
    }
}
