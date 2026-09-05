<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@teste.com',
            'password' => 'senha123',
            'role' => 'operator',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ])
            ->assertJsonPath('user.email', 'novo@teste.com')
            ->assertJsonPath('user.role', 'operator');

        $this->assertDatabaseHas('users', [
            'email' => 'novo@teste.com',
            'role' => 'operator',
        ]);
    }

    public function test_registration_always_forces_operator_role(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Tentativa Admin',
            'email' => 'hacker@teste.com',
            'password' => 'senha123',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.role', 'operator');

        $this->assertDatabaseHas('users', [
            'email' => 'hacker@teste.com',
            'role' => 'operator',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@teste.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@teste.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token'])
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'login@teste.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@teste.com',
            'password' => 'errada',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_request_is_unauthorized(): void
    {
        $response = $this->getJson('/api/customers');

        $response->assertStatus(401);
    }
}
