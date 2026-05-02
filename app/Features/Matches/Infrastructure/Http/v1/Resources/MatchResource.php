<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Resources;

use App\Features\Matches\Application\ReadModels\MatchCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MatchCard */
final class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'match_type' => $this->resource->matchType,
            'match_format' => $this->resource->matchFormat,
            'status' => $this->resource->status,
            'court_name' => $this->resource->courtName,
            'match_date' => $this->resource->matchDate?->format('Y-m-d H:i:s'),
            'notes' => $this->resource->notes,
            'created_by' => $this->resource->createdBy,
            'team_a' => $this->resource->teamA,
            'team_b' => $this->resource->teamB,
            'sets_to_win' => $this->resource->setsToWin,
            'score' => $this->resource->score,
            'elo' => $this->resource->elo?->toArray(),
            'confirmed_player_ids' => $this->resource->confirmedPlayerIds,
        ];
    }
}
