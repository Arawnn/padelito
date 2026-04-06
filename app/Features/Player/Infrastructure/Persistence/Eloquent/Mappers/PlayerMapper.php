<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Mappers;

use App\Features\Player\Domain\Entities\Player;
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
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\PreferredPosition;
use App\Features\Player\Domain\ValueObjects\TotalLosses;
use App\Features\Player\Domain\ValueObjects\TotalWins;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

final readonly class PlayerMapper
{
    public function toDomain(EloquentPlayer $player): Player
    {
        $playerPreferences = PlayerPreferences::of(
            dominantHand: DominantHand::fromDominantHandEnum($player->dominantHand),
            preferredPosition: PreferredPosition::fromPreferredPositionEnum($player->preferredPosition),
            location: Location::fromString($player->location)
        );

        $playerIdentity = PlayerIdentity::of(
            displayName: DisplayName::fromString($player->displayName),
            bio: Bio::fromString($player->bio),
            avatar: AvatarUrl::fromString($player->avatarUrl)
        );

        $playerStats = PlayerStats::of(
            totalWins: TotalWins::fromInt($player->totalWins),
            totalLosses: TotalLosses::fromInt($player->totalLosses),
            eloRating: EloRating::fromInt($player->eloRating),
            currentStreak: CurrentStreak::fromInt($player->currentStreak),
            bestStreak: BestStreak::fromInt($player->bestStreak)
        );

        return Player::reconstitute(
            id: Id::fromString($player->id),
            username: Username::fromString($player->user),
            preferences: $playerPreferences,
            identity: $playerIdentity,
            stats: $playerStats,
            padelCoins: PadelCoins::fromInt($player->padelCoins)
        );
    }

    public function toModel(Player $player): EloquentPlayer
    {
        $player = new EloquentPlayer;

        return $player->forceFill([
            'id' => $player->id()->value(),
            'username' => $player->username()->value(),
            'display_name' => $player->identity()->displayName()->value(),
            'avatar_url' => $player->identity()->avatar()->value(),
            'bio' => $player->identity()->bio()->value(),
            'elo_rating' => $player->stats()->eloRating()->value(),
            'padel_coins' => $player->padelCoins()->value(),
            'total_wins' => $player->stats()->totalWins()->value(),
            'total_losses' => $player->stats()->totalLosses()->value(),
            'current_streak' => $player->stats()->currentStreak()->value(),
            'best_streak' => $player->stats()->bestStreak()->value(),
            'location' => $player->preferences()->location()->value(),
            'dominant_hand' => $player->preferences()->dominantHand()->value(),
            'preferred_position' => $player->preferences()->preferredPosition()->value(),
        ]);
    }
}
