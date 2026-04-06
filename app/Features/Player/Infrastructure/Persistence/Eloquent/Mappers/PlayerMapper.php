<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Mappers;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use App\Features\Player\Domain\ValueObjects\BestStreak;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\CurrentStreak;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\DominantHand;
use App\Features\Player\Domain\ValueObjects\EloRating;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Location;
use App\Features\Player\Domain\ValueObjects\PadelCoins;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\PreferredPosition;
use App\Features\Player\Domain\ValueObjects\TotalLosses;
use App\Features\Player\Domain\ValueObjects\TotalWins;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

final readonly class PlayerMapper
{
    public function toDomain(EloquentPlayer $model): Player
    {
        $preferences = PlayerPreferences::of(
            dominantHand: $model->dominant_hand
                ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($model->dominant_hand))
                : null,
            preferredPosition: $model->preferred_position
                ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($model->preferred_position))
                : null,
            location: $model->location ? Location::fromString($model->location) : null,
        );

        $identity = PlayerIdentity::of(
            displayName: $model->display_name ? DisplayName::fromString($model->display_name) : null,
            bio: $model->bio ? Bio::fromString($model->bio) : null,
            avatar: $model->avatar_url ? AvatarUrl::fromString($model->avatar_url) : null,
        );

        $stats = PlayerStats::of(
            totalWins: TotalWins::fromInt($model->total_wins),
            totalLosses: TotalLosses::fromInt($model->total_losses),
            eloRating: EloRating::fromInt($model->elo_rating),
            currentStreak: CurrentStreak::fromInt($model->current_streak),
            bestStreak: BestStreak::fromInt($model->best_streak),
        );

        return Player::reconstitute(
            id: Id::fromString($model->id),
            username: Username::fromString($model->username),
            preferences: $preferences,
            identity: $identity,
            stats: $stats,
            level: PlayerLevel::fromPlayerLevelEnum(PlayerLevelEnum::from($model->level)),
            padelCoins: PadelCoins::fromInt($model->padel_coins),
        );
    }

    /**
     * Returns a flat array of persistence attributes suitable for updateOrCreate.
     *
     * @return array<string, mixed>
     */
    public function toPersistence(Player $player): array
    {
        return [
            'id' => $player->id()->value(),
            'username' => $player->username()->value(),
            'level' => $player->level()->value()->value,
            'display_name' => $player->identity()?->displayName()?->value(),
            'bio' => $player->identity()?->bio()?->value(),
            'avatar_url' => $player->identity()?->avatarUrl()?->value(),
            'dominant_hand' => $player->preferences()?->dominantHand()?->value()->value,
            'preferred_position' => $player->preferences()?->preferredPosition()?->value()->value,
            'location' => $player->preferences()?->location()?->value(),
            'elo_rating' => $player->stats()->eloRating()->value(),
            'total_wins' => $player->stats()->totalWins()->value(),
            'total_losses' => $player->stats()->totalLosses()->value(),
            'current_streak' => $player->stats()->currentStreak()->value(),
            'best_streak' => $player->stats()->bestStreak()->value(),
            'padel_coins' => $player->padelCoins()->value(),
        ];
    }
}
