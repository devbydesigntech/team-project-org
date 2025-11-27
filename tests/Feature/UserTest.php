<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected Role $executiveRole;
    protected Role $managerRole;
    protected Role $associateRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $this->organization = Organization::create(['name' => 'Test Org']);
        $this->executiveRole = Role::where('name', 'executive')->first();
        $this->managerRole = Role::where('name', 'manager')->first();
        $this->associateRole = Role::where('name', 'associate')->first();
    }

    public function test_user_can_be_created(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    public function test_user_belongs_to_organization(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(Organization::class, $user->organization);
        $this->assertEquals($this->organization->id, $user->organization->id);
    }

    public function test_user_belongs_to_role(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals('manager', $user->role->name);
    }

    public function test_is_executive_helper_method(): void
    {
        $executive = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
            'name' => 'Executive User',
            'email' => 'exec@example.com',
            'password' => bcrypt('password'),
        ]);

        $manager = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($executive->isExecutive());
        $this->assertFalse($manager->isExecutive());
    }

    public function test_is_manager_helper_method(): void
    {
        $manager = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        $associate = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Associate User',
            'email' => 'associate@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($manager->isManager());
        $this->assertFalse($associate->isManager());
    }

    public function test_is_associate_helper_method(): void
    {
        $associate = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Associate User',
            'email' => 'associate@example.com',
            'password' => bcrypt('password'),
        ]);

        $executive = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id,
            'name' => 'Executive User',
            'email' => 'exec@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($associate->isAssociate());
        $this->assertFalse($executive->isAssociate());
    }

    public function test_user_can_belong_to_multiple_teams(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Multi-team User',
            'email' => 'multi@example.com',
            'password' => bcrypt('password'),
        ]);

        $team1 = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Team 1',
        ]);

        $team2 = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Team 2',
        ]);

        $user->teams()->attach($team1->id, ['team_role' => 'Developer']);
        $user->teams()->attach($team2->id, ['team_role' => 'Designer']);

        $this->assertCount(2, $user->fresh()->teams);
        $this->assertTrue($user->teams->contains($team1));
        $this->assertTrue($user->teams->contains($team2));
    }

    public function test_user_team_pivot_includes_team_role(): void
    {
        $user = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Team Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
        ]);

        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Development Team',
        ]);

        $user->teams()->attach($team->id, ['team_role' => 'Senior Developer']);

        $userTeam = $user->fresh()->teams->first();
        $this->assertEquals('Senior Developer', $userTeam->pivot->team_role);
    }
}
