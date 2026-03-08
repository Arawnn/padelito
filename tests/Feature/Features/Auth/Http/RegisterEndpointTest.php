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
    public function testItRegistersAUser(): void
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
            ->assertJsonPath('data.user.email', 'john@example.com')
        ;

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function testItRejectsAnAlreadyUsedEmail(): void
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

    public function testItRejectsAnInvalidPassword(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
        ]);

        $response->assertStatus(422);
    }

    public function testItRejectsMissingFields(): void
    {
        $response = $this->postJson('/api/v1/register', []);
        $response->assertStatus(422);
    }
}
