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
        $this->organization = Organization::factory()->create();
        $this->role = Role::factory()->create();
    }

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ]);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'organization_id', 'role_id']
                ]
            ]);
    }

    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ];

        $response = $this->postJson('/api/v1/users', $data);

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

    public function test_can_show_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $user->id,
                'email' => $user->email
            ])
            ->assertJsonMissing(['password']);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ]);

        $data = ['name' => 'Updated User'];

        $response = $this->putJson("/api/v1/users/{$user->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated User']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ]);

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'organization_id', 'role_id']);
    }

    public function test_validation_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ]);

        $data = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'organization_id' => $this->organization->id,
            'role_id' => $this->role->id
        ];

        $response = $this->postJson('/api/v1/users', $data);

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
            'role_id' => $this->role->id
        ];

        $this->postJson('/api/v1/users', $data);

        $user = User::where('email', 'hash-test@example.com')->first();
        $this->assertNotEquals('plaintext-password', $user->password);
        $this->assertTrue(\Hash::check('plaintext-password', $user->password));
    }
}
