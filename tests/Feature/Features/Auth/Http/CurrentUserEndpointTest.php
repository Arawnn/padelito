<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Auth\Http;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CurrentUserEndpointTest extends FeatureTestCase
{
    public function test_it_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create([
            'id' => '00000000-0000-0000-0000-000000000123',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email']],
            ])
            ->assertJsonPath('data.user.id', '00000000-0000-0000-0000-000000000123')
            ->assertJsonPath('data.user.name', 'John Doe')
            ->assertJsonPath('data.user.email', 'john@example.com');
    }

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401);
    }
}
