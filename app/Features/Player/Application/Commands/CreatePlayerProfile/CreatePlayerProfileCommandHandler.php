<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\CreatePlayerProfile;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\DominantHand;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Location;
use App\Features\Player\Domain\ValueObjects\PadelCoins;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Features\Player\Domain\ValueObjects\PreferredPosition;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class CreatePlayerProfileCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(CreatePlayerProfileCommand $command): Player
    {
        $userId = Id::fromString($command->userId);

        if ($this->playerRepository->findById($userId)) {
            throw PlayerProfileAlreadyExistException::create();
        }

        if ($this->playerRepository->findByUsername(Username::fromString($command->username))) {
            throw PlayerProfileAlreadyExistException::create();
        }

        $preferences = PlayerPreferences::of(
            dominantHand: $command->dominantHand ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($command->dominantHand)) : null,
            preferredPosition: $command->preferredPosition ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($command->preferredPosition)) : null,
            location: $command->location ? Location::fromString($command->location) : null,
        );

        $identity = PlayerIdentity::of(
            displayName: $command->displayName ? DisplayName::fromString($command->displayName) : null,
            bio: $command->bio ? Bio::fromString($command->bio) : null,
            avatar: null,
        );

        $player = Player::create(
            id: $userId,
            username: Username::fromString($command->username),
            level: PlayerLevel::fromPlayerLevelEnum(PlayerLevelEnum::from($command->level)),
            preferences: $preferences,
            identity: $identity,
            stats: PlayerStats::initialize(),
            padelCoins: PadelCoins::initialize(),
        );

        $this->playerRepository->save($player);

        $domainEvents = $player->pullDomainEvents();
        $this->eventDispatcher->dispatchEvents($domainEvents);

        return $player;
    }
}
