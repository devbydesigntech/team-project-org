<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected Role $managerRole;
    protected Role $associateRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $this->organization = Organization::create(['name' => 'Test Org']);
        $this->managerRole = Role::where('name', 'manager')->first();
        $this->associateRole = Role::where('name', 'associate')->first();
    }

    public function test_team_can_be_created(): void
    {
        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Team',
        ]);

        $this->assertDatabaseHas('teams', [
            'name' => 'Test Team',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_team_belongs_to_organization(): void
    {
        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Team',
        ]);

        $this->assertInstanceOf(Organization::class, $team->organization);
        $this->assertEquals($this->organization->id, $team->organization->id);
    }

    public function test_team_can_have_a_manager(): void
    {
        $manager = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->managerRole->id,
            'name' => 'Team Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        $team = Team::create([
            'organization_id' => $this->organization->id,
            'manager_id' => $manager->id,
            'name' => 'Managed Team',
        ]);

        $this->assertInstanceOf(User::class, $team->manager);
        $this->assertEquals($manager->id, $team->manager->id);
        $this->assertEquals('Team Manager', $team->manager->name);
    }

    public function test_team_can_have_multiple_members(): void
    {
        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Development Team',
        ]);

        $member1 = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Member 1',
            'email' => 'member1@example.com',
            'password' => bcrypt('password'),
        ]);

        $member2 = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Member 2',
            'email' => 'member2@example.com',
            'password' => bcrypt('password'),
        ]);

        $team->members()->attach($member1->id, ['team_role' => 'Developer']);
        $team->members()->attach($member2->id, ['team_role' => 'Senior Developer']);

        $this->assertCount(2, $team->fresh()->members);
        $this->assertTrue($team->members->contains($member1));
        $this->assertTrue($team->members->contains($member2));
    }

    public function test_team_member_has_team_role(): void
    {
        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Engineering Team',
        ]);

        $member = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Engineer',
            'email' => 'engineer@example.com',
            'password' => bcrypt('password'),
        ]);

        $team->members()->attach($member->id, ['team_role' => 'Lead Engineer']);

        $teamMember = $team->fresh()->members->first();
        $this->assertEquals('Lead Engineer', $teamMember->pivot->team_role);
    }

    public function test_team_member_relationship_is_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $team = Team::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Team',
        ]);

        $member = User::create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->associateRole->id,
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt('password'),
        ]);

        // First attachment should work
        $team->members()->attach($member->id, ['team_role' => 'Developer']);
        
        // Second attachment should fail due to unique constraint
        $team->members()->attach($member->id, ['team_role' => 'Designer']);
    }

    public function test_seeded_data_is_correct(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);

        $organization = Organization::where('name', 'Dreamers Syndicate')->first();
        $this->assertNotNull($organization);

        $teams = Team::where('organization_id', $organization->id)->get();
        $this->assertCount(2, $teams);

        $engineeringTeam = Team::where('name', 'Engineering Team')->first();
        $this->assertNotNull($engineeringTeam);
        $this->assertNotNull($engineeringTeam->manager);
        $this->assertEquals('Bob Manager', $engineeringTeam->manager->name);
        $this->assertCount(2, $engineeringTeam->members);

        $designTeam = Team::where('name', 'Design Team')->first();
        $this->assertNotNull($designTeam);
        $this->assertNotNull($designTeam->manager);
        $this->assertEquals('Carol Manager', $designTeam->manager->name);
        $this->assertCount(2, $designTeam->members);
    }
}
