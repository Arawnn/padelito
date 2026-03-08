<?php

namespace Tests\Feature\Features\Auth\Http;

use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LogoutEndpointTest extends FeatureTestCase
{
    public function testItLogsOutAnAuthenticatedUser(): void
    {
        $register = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $token = $register->json('token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout')
        ;

        $response->assertStatus(200);
    }

    public function testItRejectsUnauthenticatedLogout(): void
    {
        $response = $this->postJson('/api/v1/logout');
        $response->assertStatus(401);
    }
}
