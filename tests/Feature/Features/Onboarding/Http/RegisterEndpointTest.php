<?php

namespace Tests\Feature\Features\Onboarding\Http;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Infrastructure\Exceptions\InfrastructureException;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RegisterEndpointTest extends FeatureTestCase
{
    private const ENDPOINT = '/api/v1/register';

    private const NAME = 'John Doe';

    private const EMAIL = 'john@example.com';

    private const PASSWORD = 'Password123!';

    public function test_it_registers_a_user(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email']],
                'token',
                'message',
            ])
            ->assertJsonPath('data.user.email', self::EMAIL);

        $this->assertDatabaseHas('users', ['email' => self::EMAIL]);
    }

    public function test_it_creates_a_default_player_profile_on_registration(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(201);

        $userId = $response->json('data.user.id');

        $this->assertDatabaseHas('profiles', [
            'id' => $userId,
            'level' => 'beginner',
            'display_name' => self::NAME,
        ]);
    }

    public function test_it_rejects_an_already_used_email(): void
    {
        $this->postJson(self::ENDPOINT, $this->validPayload());

        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(422);
    }

    public function test_it_rejects_an_invalid_password(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload(['password' => 'weak']));

        $response->assertStatus(422);
    }

    public function test_it_rejects_missing_fields(): void
    {
        $response = $this->postJson(self::ENDPOINT, []);
        $response->assertStatus(422);
    }

    public function test_it_rolls_back_user_if_player_profile_initialization_fails(): void
    {
        $this->app->bind(PlayerRepositoryInterface::class, fn () => new class implements PlayerRepositoryInterface
        {
            public function findById(Id $_id): ?Player
            {
                return null;
            }

            public function findByUsername(Username $_username): ?Player
            {
                return null;
            }

            public function save(Player $_player): void
            {
                throw InfrastructureException::handlerNotFound('simulated player persistence failure');
            }
        });

        $this->postJson(self::ENDPOINT, $this->validPayload());

        $this->assertDatabaseMissing('users', ['email' => self::EMAIL]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => self::NAME,
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ], $overrides);
    }
}
