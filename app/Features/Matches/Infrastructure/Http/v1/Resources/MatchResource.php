<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Resources;

use App\Features\Matches\Infrastructure\Http\v1\ViewModels\MatchView;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MatchView */
final class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $match = $this->resource->match;

        return [
            'id' => $match->id()->value(),
            'match_type' => $match->type()->value()->value,
            'match_format' => $match->format()->value()->value,
            'status' => $match->status()->value()->value,
            'court_name' => $match->courtName()?->value(),
            'match_date' => $match->matchDate()?->format('Y-m-d H:i:s'),
            'notes' => $match->notes()?->value(),
            'created_by' => $match->createdBy()->value(),
            'team_a' => [
                'player1_id' => $match->teamAPlayer1Id()->value(),
                'player2_id' => $match->teamAPlayer2Id()?->value(),
            ],
            'team_b' => [
                'player1_id' => $match->teamBPlayer1Id()?->value(),
                'player2_id' => $match->teamBPlayer2Id()?->value(),
            ],
            'sets_to_win' => $match->setsToWin()->value(),
            'score' => [
                'team_a' => $match->teamAScore()?->value(),
                'team_b' => $match->teamBScore()?->value(),
                'sets_detail' => $match->setsDetail()?->sets(),
            ],
            'elo' => $this->resource->elo?->toArray(),
            'confirmed_player_ids' => array_map(fn ($id) => $id->value(), $match->confirmedPlayerIds()),
        ];
    }
}
