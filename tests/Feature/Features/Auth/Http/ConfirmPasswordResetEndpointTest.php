<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Auth\Http;

use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Infrastructure\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConfirmPasswordResetEndpointTest extends FeatureTestCase
{
    private PasswordResetTokenRepositoryInterface $tokenRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenRepository = app(PasswordResetTokenRepositoryInterface::class);
    }

    public function test_it_resets_the_password(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = $this->tokenRepository->create(Email::fromString('john@example.com'));

        $response = $this->postJson('/api/v1/reset-password/confirm', [
            'email' => 'john@example.com',
            'token' => $token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Password has been reset successfully.');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'john@example.com']);

        $updatedUser = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('NewPassword123!', $updatedUser->password));
    }

    public function test_it_returns_not_found_if_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/reset-password/confirm', [
            'email' => 'unknown@example.com',
            'token' => 'any-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(404);
    }

    public function test_it_rejects_an_invalid_token(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/reset-password/confirm', [
            'email' => 'john@example.com',
            'token' => 'invalid-or-expired-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_non_matching_password_confirmation(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/reset-password/confirm', [
            'email' => 'john@example.com',
            'token' => 'any-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/reset-password/confirm', []);

        $response->assertStatus(422);
    }
}
