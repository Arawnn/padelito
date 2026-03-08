<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Auth\Http;

use App\Features\Auth\Infrastructure\Models\User;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SendPasswordResetEmailEndpointTest extends FeatureTestCase
{
    public function testItReturnsSuccessWhenUserExists(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message'])
            ->assertJsonPath('message', 'If an account with that email exists, a password reset link has been sent.')
        ;

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'john@example.com']);
    }

    public function testItReturnsSuccessEvenWhenUserDoesNotExist(): void
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'If an account with that email exists, a password reset link has been sent.')
        ;

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'unknown@example.com']);
    }

    public function testItRejectsMissingEmail(): void
    {
        $response = $this->postJson('/api/v1/reset-password', []);

        $response->assertStatus(422);
    }

    public function testItRejectsAnInvalidEmailFormat(): void
    {
        $response = $this->postJson('/api/v1/reset-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }
}
