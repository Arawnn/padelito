<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Matches\Http;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MatchEndpointsTest extends FeatureTestCase
{
    private User $creator;

    private string $creatorProfileId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create();
        $this->creatorProfileId = $this->creator->id;

        // Create a player profile for the creator via the API, then clear auth state.
        Sanctum::actingAs($this->creator);
        $this->postJson('/api/v1/player', [
            'username' => 'creator_player',
            'displayName' => 'Creator',
            'level' => 'intermediate',
        ]);
        $this->app['auth']->forgetGuards();
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function test_creator_can_create_a_match(): void
    {
        Sanctum::actingAs($this->creator);

        $response = $this->postJson('/api/v1/matches', [
            'match_type' => 'friendly',
            'match_format' => 'doubles',
            'court_name' => 'Court Centrale',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.match_type', 'friendly')
            ->assertJsonPath('data.team_a.player1_id', $this->creatorProfileId);

        $this->assertDatabaseHas('matches', ['created_by' => $this->creatorProfileId]);
    }

    public function test_create_match_requires_authentication(): void
    {
        $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles'])
            ->assertStatus(401);
    }

    public function test_create_match_validates_match_type(): void
    {
        Sanctum::actingAs($this->creator);

        $this->postJson('/api/v1/matches', ['match_type' => 'invalid', 'match_format' => 'doubles'])
            ->assertStatus(422);
    }

    // ─── Get ─────────────────────────────────────────────────────────────────

    public function test_can_get_a_match_by_id(): void
    {
        Sanctum::actingAs($this->creator);

        $created = $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles']);
        $matchId = $created->json('data.id');

        $this->getJson("/api/v1/matches/{$matchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $matchId);
    }

    public function test_get_match_returns_404_for_unknown_id(): void
    {
        Sanctum::actingAs($this->creator);

        $this->getJson('/api/v1/matches/99999999-9999-9999-9999-999999999999')
            ->assertStatus(404);
    }

    // ─── Cancel ──────────────────────────────────────────────────────────────

    public function test_creator_can_cancel_a_match(): void
    {
        Sanctum::actingAs($this->creator);

        $matchId = $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles'])
            ->json('data.id');

        $this->postJson("/api/v1/matches/{$matchId}/cancel")
            ->assertStatus(204);

        $this->assertDatabaseHas('matches', ['id' => $matchId, 'status' => 'cancelled']);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function test_creator_can_update_court_name(): void
    {
        Sanctum::actingAs($this->creator);

        $matchId = $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles'])
            ->json('data.id');

        $this->patchJson("/api/v1/matches/{$matchId}", ['court_name' => 'Updated Court'])
            ->assertStatus(200)
            ->assertJsonPath('data.court_name', 'Updated Court');
    }

    // ─── GetMyMatches ─────────────────────────────────────────────────────────

    public function test_player_can_list_their_matches(): void
    {
        Sanctum::actingAs($this->creator);

        $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles']);
        $this->postJson('/api/v1/matches', ['match_type' => 'ranked', 'match_format' => 'singles']);

        $response = $this->getJson('/api/v1/player/me/matches');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_player_can_filter_matches_by_pending(): void
    {
        Sanctum::actingAs($this->creator);

        $matchId = $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles'])
            ->json('data.id');

        // Cancel one to change its status
        $this->postJson("/api/v1/matches/{$matchId}/cancel");

        $this->postJson('/api/v1/matches', ['match_type' => 'friendly', 'match_format' => 'doubles']);

        $response = $this->getJson('/api/v1/player/me/matches?filter=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
