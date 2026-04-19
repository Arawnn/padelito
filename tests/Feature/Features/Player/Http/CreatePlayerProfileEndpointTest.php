<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Player\Http;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreatePlayerProfileEndpointTest extends FeatureTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Use factory to bypass the domain layer — no UserCreated event fired,
        // so no auto-profile is created. Allows testing the batch endpoint cleanly.
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_it_creates_a_player_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/player', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['player' => ['id']],
                'message',
            ])
            ->assertJsonPath('message', 'Player profile created successfully');

        $this->assertDatabaseHas('profiles', [
            'username' => 'jean_dupont',
            'level' => 'beginner',
        ]);
        $this->assertNull(DB::table('profiles')->where('id', $this->user->id)->value('avatar_url'));
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->postJson('/api/v1/player', $this->validPayload())
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Conflict
    // -------------------------------------------------------------------------

    public function test_it_rejects_duplicate_profile_for_same_user(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload());

        $this->postJson('/api/v1/player', $this->validPayload(['username' => 'other_user']))
            ->assertStatus(409);
    }

    public function test_it_rejects_duplicate_username(): void
    {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload());

        Sanctum::actingAs($otherUser);
        $this->postJson('/api/v1/player', $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    // -------------------------------------------------------------------------
    // Validation — required fields
    // -------------------------------------------------------------------------

    public function test_it_rejects_missing_username(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => null]))
            ->assertStatus(422);
    }

    public function test_it_rejects_missing_level(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['level' => null]))
            ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Validation — username format
    // -------------------------------------------------------------------------

    public function test_it_rejects_username_with_uppercase(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => 'Jean_Dupont']))
            ->assertStatus(422)
            ->assertJsonPath('error.details.username.0', 'The username may only contain lowercase letters, digits and underscores.');
    }

    public function test_it_rejects_username_too_long(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => str_repeat('a', 31)]))
            ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Validation — level enum
    // -------------------------------------------------------------------------

    public function test_it_rejects_invalid_level(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['level' => 'god']))
            ->assertStatus(422);
    }

    public function test_it_accepts_all_valid_levels(): void
    {
        foreach (['beginner', 'intermediate', 'advanced', 'confirmed', 'expert'] as $i => $level) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            $this->postJson('/api/v1/player', $this->validPayload([
                'username' => 'player_'.$i,
                'level' => $level,
            ]))->assertStatus(201);
        }
    }

    // -------------------------------------------------------------------------
    // Validation — HTML injection stripping
    // -------------------------------------------------------------------------

    public function test_it_strips_html_tags_from_bio(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload([
            'bio' => '<p><strong>Passionné</strong> de padel</p>',
        ]))->assertStatus(201);

        $this->assertDatabaseHas('profiles', ['bio' => 'Passionné de padel']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'jean_dupont',
            'displayName' => 'Jean Dupont',
            'level' => 'beginner',
            'dominantHand' => 'right',
            'preferredPosition' => 'back',
        ], $overrides);
    }
}
