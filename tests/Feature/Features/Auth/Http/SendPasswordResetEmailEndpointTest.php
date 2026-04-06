<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Auth\Http;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\User;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SendPasswordResetEmailEndpointTest extends FeatureTestCase
{
    public function test_it_returns_success_when_user_exists(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message'])
            ->assertJsonPath('message', 'If an account with that email exists, a password reset link has been sent.');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'john@example.com']);
    }

    public function test_it_returns_success_even_when_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'If an account with that email exists, a password reset link has been sent.');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'unknown@example.com']);
    }

    public function test_it_rejects_missing_email(): void
    {
        $response = $this->postJson('/api/v1/reset-password', []);

        $response->assertStatus(422);
    }

    public function test_it_rejects_an_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }
}
