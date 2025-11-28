<?php

namespace Tests\Feature\Api;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->executiveRole = Role::firstOrCreate(['name' => 'executive']);
        $this->managerRole = Role::firstOrCreate(['name' => 'manager']);
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

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $response = $this->actingAs($this->manager)->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'organization_id', 'role_id']
                ]
            ]);
    }

    public function test_executive_can_create_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/users', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test User',
                'email' => 'test@example.com'
            ])
            ->assertJsonMissing(['password']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'organization_id' => $this->organization->id
        ]);
    }

    public function test_non_executive_cannot_create_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ];

        $response = $this->actingAs($this->manager)
            ->postJson('/api/v1/users', $data);

        $response->assertStatus(403);
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $response = $this->actingAs($this->manager)->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $user->id,
                'email' => $user->email
            ])
            ->assertJsonMissing(['password']);
    }

    public function test_executive_can_update_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $data = ['name' => 'Updated User'];

        $response = $this->actingAs($this->executive)
            ->putJson("/api/v1/users/{$user->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated User']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User'
        ]);
    }

    public function test_non_executive_cannot_update_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $data = ['name' => 'Updated User'];

        $response = $this->actingAs($this->manager)
            ->putJson("/api/v1/users/{$user->id}", $data);

        $response->assertStatus(403);
    }

    public function test_executive_can_delete_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $response = $this->actingAs($this->executive)
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_non_executive_cannot_delete_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $response = $this->actingAs($this->manager)
            ->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'organization_id', 'role_id']);
    }

    public function test_validation_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ]);

        $data = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ];

        $response = $this->actingAs($this->executive)
            ->postJson('/api/v1/users', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_is_hashed_when_creating_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'hash-test@example.com',
            'password' => 'plaintext-password',
            'organization_id' => $this->organization->id,
            'role_id' => $this->executiveRole->id
        ];

        $this->actingAs($this->executive)
            ->postJson('/api/v1/users', $data);

        $user = User::where('email', 'hash-test@example.com')->first();
        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Hash::check('plaintext-password', $user->password));
    }
}
