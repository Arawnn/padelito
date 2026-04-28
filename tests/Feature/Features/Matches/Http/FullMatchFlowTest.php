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
final class FullMatchFlowTest extends FeatureTestCase
{
    private User $creator;

    private User $player2;

    private User $player3;

    private User $player4;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create();
        $this->player2 = User::factory()->create();
        $this->player3 = User::factory()->create();
        $this->player4 = User::factory()->create();

        $this->registerProfile($this->creator, 'creator_user');
        $this->registerProfile($this->player2, 'player_two');
        $this->registerProfile($this->player3, 'player_three');
        $this->registerProfile($this->player4, 'player_four');
    }

    public function test_full_doubles_friendly_match_flow(): void
    {
        // 1. Creator creates a match
        Sanctum::actingAs($this->creator);
        $matchId = $this->postJson('/api/v1/matches', [
            'match_type' => 'friendly',
            'match_format' => 'doubles',
        ])->assertStatus(201)->json('data.id');

        // 2. Creator invites 3 other players
        $inv2 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player2->id,
            'type' => 'partner',
        ])->assertStatus(201)->json('data.id');

        $inv3 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player3->id,
            'type' => 'opponent',
        ])->assertStatus(201)->json('data.id');

        $inv4 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player4->id,
            'type' => 'opponent',
        ])->assertStatus(201)->json('data.id');

        // 3. All 3 players accept their invitation
        Sanctum::actingAs($this->player2);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv2}", ['accept' => true])
            ->assertStatus(204);

        Sanctum::actingAs($this->player3);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv3}", ['accept' => true])
            ->assertStatus(204);

        Sanctum::actingAs($this->player4);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv4}", ['accept' => true])
            ->assertStatus(204);

        // 4. Creator updates the score
        Sanctum::actingAs($this->creator);
        $this->patchJson("/api/v1/matches/{$matchId}", [
            'sets_detail' => [
                ['a' => 6, 'b' => 3],
                ['a' => 6, 'b' => 2],
            ],
        ])->assertStatus(200);

        // 5. All 4 players confirm
        foreach ([$this->creator, $this->player2, $this->player3, $this->player4] as $user) {
            Sanctum::actingAs($user);
            $this->postJson("/api/v1/matches/{$matchId}/confirm")
                ->assertStatus(204);
        }

        // 6. Match is now validated
        Sanctum::actingAs($this->creator);
        $this->getJson("/api/v1/matches/{$matchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'validated')
            ->assertJsonPath('data.score.team_a', 2)
            ->assertJsonPath('data.score.team_b', 0);

        $this->assertDatabaseHas('matches', ['id' => $matchId, 'status' => 'validated']);
    }

    public function test_player_can_list_pending_invitations(): void
    {
        Sanctum::actingAs($this->creator);
        $matchId = $this->postJson('/api/v1/matches', [
            'match_type' => 'friendly',
            'match_format' => 'doubles',
        ])->json('data.id');

        $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player2->id,
            'type' => 'opponent',
        ]);

        Sanctum::actingAs($this->player2);
        $response = $this->getJson('/api/v1/player/me/invitations');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    public function test_declining_invitation_removes_it_from_pending_list(): void
    {
        Sanctum::actingAs($this->creator);
        $matchId = $this->postJson('/api/v1/matches', [
            'match_type' => 'friendly',
            'match_format' => 'doubles',
        ])->json('data.id');

        $invId = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player2->id,
            'type' => 'opponent',
        ])->json('data.id');

        Sanctum::actingAs($this->player2);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$invId}", ['accept' => false]);

        $this->getJson('/api/v1/player/me/invitations')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_ranked_match_exposes_projected_then_confirmed_elo_summary(): void
    {
        Sanctum::actingAs($this->creator);
        $matchId = $this->postJson('/api/v1/matches', [
            'match_type' => 'ranked',
            'match_format' => 'doubles',
        ])->assertStatus(201)->json('data.id');

        $inv2 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player2->id,
            'type' => 'partner',
        ])->assertStatus(201)->json('data.id');

        $inv3 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player3->id,
            'type' => 'opponent',
        ])->assertStatus(201)->json('data.id');

        $inv4 = $this->postJson("/api/v1/matches/{$matchId}/invitations", [
            'invitee_id' => $this->player4->id,
            'type' => 'opponent',
        ])->assertStatus(201)->json('data.id');

        Sanctum::actingAs($this->player2);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv2}", ['accept' => true])
            ->assertStatus(204);

        Sanctum::actingAs($this->player3);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv3}", ['accept' => true])
            ->assertStatus(204);

        Sanctum::actingAs($this->player4);
        $this->patchJson("/api/v1/matches/{$matchId}/invitations/{$inv4}", ['accept' => true])
            ->assertStatus(204);

        Sanctum::actingAs($this->creator);
        $this->patchJson("/api/v1/matches/{$matchId}", [
            'sets_detail' => [
                ['a' => 6, 'b' => 3],
                ['a' => 4, 'b' => 6],
                ['a' => 7, 'b' => 5],
            ],
        ])->assertStatus(200);

        $this->getJson("/api/v1/matches/{$matchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.elo.team_a_before', 1500)
            ->assertJsonPath('data.elo.team_b_before', 1500)
            ->assertJsonPath('data.elo.team_a_change', 20)
            ->assertJsonPath('data.elo.team_b_change', -20)
            ->assertJsonPath('data.elo.current_user_change', 20)
            ->assertJsonPath('data.elo.source', 'projected');

        foreach ([$this->creator, $this->player2, $this->player3, $this->player4] as $user) {
            Sanctum::actingAs($user);
            $this->postJson("/api/v1/matches/{$matchId}/confirm")
                ->assertStatus(204);
        }

        Sanctum::actingAs($this->player3);
        $this->getJson("/api/v1/matches/{$matchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'validated')
            ->assertJsonPath('data.elo.team_a_before', 1500)
            ->assertJsonPath('data.elo.team_b_before', 1500)
            ->assertJsonPath('data.elo.team_a_change', 20)
            ->assertJsonPath('data.elo.team_b_change', -20)
            ->assertJsonPath('data.elo.current_user_change', -20)
            ->assertJsonPath('data.elo.source', 'confirmed');
    }

    private function registerProfile(User $user, string $username): void
    {
        Sanctum::actingAs($user);
        $this->postJson('/api/v1/player', [
            'username' => $username,
            'displayName' => ucwords(str_replace('_', ' ', $username)),
            'level' => 'intermediate',
        ]);
        $this->app['auth']->forgetGuards();
    }
}
