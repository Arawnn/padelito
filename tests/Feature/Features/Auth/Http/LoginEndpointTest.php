<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Auth\Http;

use App\Features\Auth\Infrastructure\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LoginEndpointTest extends FeatureTestCase
{
    public function test_it_logs_in_a_user(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email']],
                'token',
                'message',
            ])
            ->assertJsonPath('data.user.email', 'john@example.com');
    }

    public function test_it_rejects_wrong_password(): void
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword!1',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_non_existent_user(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(404);
    }

    public function test_it_rejects_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/login', []);
        $response->assertStatus(422);
    }
}
