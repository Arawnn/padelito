<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Resources;

use App\Features\Matches\Domain\Entities\PadelMatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PadelMatch */
final class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id()->value(),
            'match_type' => $this->type()->value()->value,
            'match_format' => $this->format()->value()->value,
            'status' => $this->status()->value()->value,
            'court_name' => $this->courtName()?->value(),
            'match_date' => $this->matchDate()?->format('Y-m-d H:i:s'),
            'notes' => $this->notes()?->value(),
            'created_by' => $this->createdBy()->value(),
            'team_a' => [
                'player1_id' => $this->teamAPlayer1Id()->value(),
                'player2_id' => $this->teamAPlayer2Id()?->value(),
            ],
            'team_b' => [
                'player1_id' => $this->teamBPlayer1Id()?->value(),
                'player2_id' => $this->teamBPlayer2Id()?->value(),
            ],
            'sets_to_win' => $this->setsToWin()->value(),
            'score' => [
                'team_a' => $this->teamAScore()?->value(),
                'team_b' => $this->teamBScore()?->value(),
                'sets_detail' => $this->setsDetail()?->sets(),
            ],
            'elo' => [
                'team_a_before' => $this->teamAEloBefore()?->value(),
                'team_b_before' => $this->teamBEloBefore()?->value(),
                'change' => $this->eloChange()?->value(),
            ],
            'confirmed_player_ids' => array_map(fn ($id) => $id->value(), $this->confirmedPlayerIds()),
        ];
    }
}
