<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Resources;

use App\Features\Player\Domain\Entities\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Player */
final class PublicPlayerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username()->value(),
            'level' => $this->level()->value()->value,
            'identity' => [
                'display_name' => $this->identity()?->displayName()?->value(),
                'bio' => $this->identity()?->bio()?->value(),
                'avatar_url' => $this->identity()?->avatarUrl()?->value(),
            ],
            'preferences' => [
                'dominant_hand' => $this->preferences()?->dominantHand()?->value()->value,
                'preferred_position' => $this->preferences()?->preferredPosition()?->value()->value,
                'location' => $this->preferences()?->location()?->value(),
            ],
            'stats' => [
                'elo_rating' => $this->stats()->eloRating()->value(),
                'total_wins' => $this->stats()->totalWins()->value(),
                'total_losses' => $this->stats()->totalLosses()->value(),
                'total_matches' => $this->stats()->totalMatches(),
                'win_rate' => $this->stats()->winRate(),
                'current_streak' => $this->stats()->currentStreak()->value(),
                'best_streak' => $this->stats()->bestStreak()->value(),
            ],
        ];
    }
}
