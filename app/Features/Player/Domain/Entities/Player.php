<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Entities;

use App\Features\Player\Domain\Events\PlayerProfileCreated;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PadelCoins;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Domain\Entities\AggregateRoot;

final class Player extends AggregateRoot
{
    private function __construct(
        private readonly Id $id,
        private Username $username,
        private ?PlayerPreferences $preferences,
        private ?PlayerIdentity $identity,
        private PlayerStats $stats,
        private PlayerLevel $level,
        private PadelCoins $padelCoins,
    ) {}

    public static function create(Id $id, Username $username, ?PlayerPreferences $preferences, ?PlayerIdentity $identity, PlayerStats $stats, PlayerLevel $level, PadelCoins $padelCoins): self
    {
        $profile = new self(
            id: $id,
            username: $username,
            preferences: $preferences,
            identity: $identity,
            stats: $stats,
            level: $level,
            padelCoins: $padelCoins,
        );

        $profile->recordDomainEvent(new PlayerProfileCreated($profile->id()->value(), $profile->username()->value()));

        return $profile;
    }

    public static function reconstitute(Id $id, Username $username, ?PlayerPreferences $preferences, ?PlayerIdentity $identity, PlayerStats $stats, PlayerLevel $level, PadelCoins $padelCoins): self
    {
        return new self(
            $id,
            $username,
            $preferences,
            $identity,
            $stats,
            $level,
            $padelCoins,
        );
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function username(): Username
    {
        return $this->username;
    }

    public function level(): PlayerLevel
    {
        return $this->level;
    }

    public function preferences(): ?PlayerPreferences
    {
        return $this->preferences;
    }

    public function identity(): ?PlayerIdentity
    {
        return $this->identity;
    }

    public function stats(): PlayerStats
    {
        return $this->stats;
    }

    public function padelCoins(): PadelCoins
    {
        return $this->padelCoins;
    }
}
