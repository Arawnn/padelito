<?php

declare(strict_types=1);

namespace Tests\Shared\Mother;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\DominantHand;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PadelCoins;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\PreferredPosition;
use App\Features\Player\Domain\ValueObjects\Username;

final class PlayerMother
{
    private string $id = '00000000-0000-0000-0000-000000000001';

    private string $username = 'jean_dupont';

    private string $level = 'beginner';

    private ?string $displayName = 'Jean Dupont';

    private ?string $bio = null;

    private ?string $dominantHand = 'right';

    private ?string $preferredPosition = 'back';

    private function __construct() {}

    public static function create(): self
    {
        return new self;
    }

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withUsername(string $username): self
    {
        $clone = clone $this;
        $clone->username = $username;

        return $clone;
    }

    public function withLevel(string $level): self
    {
        $clone = clone $this;
        $clone->level = $level;

        return $clone;
    }

    public function build(): Player
    {
        $preferences = PlayerPreferences::of(
            dominantHand: $this->dominantHand
                ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($this->dominantHand))
                : null,
            preferredPosition: $this->preferredPosition
                ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($this->preferredPosition))
                : null,
            location: null,
        );

        $identity = PlayerIdentity::of(
            displayName: $this->displayName ? DisplayName::fromString($this->displayName) : null,
            bio: $this->bio ? Bio::fromString($this->bio) : null,
            avatar: null,
        );

        return Player::reconstitute(
            id: Id::fromString($this->id),
            username: Username::fromString($this->username),
            preferences: $preferences,
            identity: $identity,
            stats: PlayerStats::initialize(),
            level: PlayerLevel::fromPlayerLevelEnum(PlayerLevelEnum::from($this->level)),
            padelCoins: PadelCoins::initialize(),
        );
    }
}
