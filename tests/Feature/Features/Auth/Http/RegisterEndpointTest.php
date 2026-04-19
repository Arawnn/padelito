<?php

namespace Tests\Feature\Features\Auth\Http;

use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RegisterEndpointTest extends FeatureTestCase
{
    public function test_it_registers_a_user(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email']],
                'token',
                'message',
            ])
            ->assertJsonPath('data.user.email', 'john@example.com');

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_it_creates_a_default_player_profile_on_registration(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201);

        $userId = $response->json('data.user.id');

        $this->assertDatabaseHas('profiles', [
            'id' => $userId,
            'level' => 'beginner',
            'display_name' => 'John Doe',
        ]);
    }

    public function test_it_rejects_an_already_used_email(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_an_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);
        $response->assertStatus(422);
    }
}
