<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_roles_are_seeded(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'executive']);
        $this->assertDatabaseHas('roles', ['name' => 'manager']);
        $this->assertDatabaseHas('roles', ['name' => 'associate']);
    }

    public function test_all_three_roles_exist(): void
    {
        $roles = Role::all();
        
        $this->assertCount(3, $roles);
        $this->assertTrue($roles->contains('name', 'executive'));
        $this->assertTrue($roles->contains('name', 'manager'));
        $this->assertTrue($roles->contains('name', 'associate'));
    }

    public function test_role_has_users_relationship(): void
    {
        $organization = Organization::create(['name' => 'Test Org']);
        $role = Role::where('name', 'executive')->first();

        $user = User::create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
            'name' => 'Test Executive',
            'email' => 'exec@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($role->users->contains($user));
        $this->assertEquals($role->id, $user->role_id);
    }

    public function test_role_name_is_unique(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Role::create(['name' => 'executive']);
    }
}
