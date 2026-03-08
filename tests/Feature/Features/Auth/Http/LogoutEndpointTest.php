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
    public function test_it_logs_out_an_authenticated_user(): void
    {
        $register = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $token = $register->json('token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);
    }

    public function test_it_rejects_unauthenticated_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');
        $response->assertStatus(401);
    }
}
