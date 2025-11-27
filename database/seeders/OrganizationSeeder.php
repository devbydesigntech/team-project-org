<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an organization
        $org = Organization::create([
            'name' => 'Dreamers Syndicate',
        ]);

        // Get roles
        $executiveRole = Role::where('name', 'executive')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $associateRole = Role::where('name', 'associate')->first();

        // Create an executive
        $executive = User::create([
            'organization_id' => $org->id,
            'role_id' => $executiveRole->id,
            'name' => 'Alice Executive',
            'email' => 'alice@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        // Create managers
        $manager1 = User::create([
            'organization_id' => $org->id,
            'role_id' => $managerRole->id,
            'name' => 'Bob Manager',
            'email' => 'bob@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        $manager2 = User::create([
            'organization_id' => $org->id,
            'role_id' => $managerRole->id,
            'name' => 'Carol Manager',
            'email' => 'carol@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        // Create associates
        $associate1 = User::create([
            'organization_id' => $org->id,
            'role_id' => $associateRole->id,
            'name' => 'David Associate',
            'email' => 'david@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        $associate2 = User::create([
            'organization_id' => $org->id,
            'role_id' => $associateRole->id,
            'name' => 'Eve Associate',
            'email' => 'eve@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        $associate3 = User::create([
            'organization_id' => $org->id,
            'role_id' => $associateRole->id,
            'name' => 'Frank Associate',
            'email' => 'frank@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        $associate4 = User::create([
            'organization_id' => $org->id,
            'role_id' => $associateRole->id,
            'name' => 'Grace Associate',
            'email' => 'grace@dreamers.com',
            'password' => Hash::make('password'),
        ]);

        // Create teams
        $team1 = Team::create([
            'organization_id' => $org->id,
            'manager_id' => $manager1->id,
            'name' => 'Engineering Team',
        ]);

        $team2 = Team::create([
            'organization_id' => $org->id,
            'manager_id' => $manager2->id,
            'name' => 'Design Team',
        ]);

        // Attach team members
        $team1->members()->attach([
            $associate1->id => ['team_role' => 'Senior Developer'],
            $associate2->id => ['team_role' => 'Developer'],
        ]);

        $team2->members()->attach([
            $associate3->id => ['team_role' => 'Senior Designer'],
            $associate4->id => ['team_role' => 'Designer'],
        ]);
    }
}
