<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerIdentity;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class UpdatePlayerIdentityCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(UpdatePlayerIdentityCommand $command): Player
    {
        $userId = Id::fromString($command->userId);

        $player = $this->playerRepository->findById($userId);
        if (! $player) {
            throw PlayerProfileNotFoundException::create();
        }

        $current = $player->identity();

        if ($command->displayName->isPresent()) {
            $raw = $command->displayName->value();
            $displayName = $raw !== null ? DisplayName::fromString($raw) : null;
        } else {
            $displayName = $current?->displayName();
        }

        if ($command->bio->isPresent()) {
            $raw = $command->bio->value();
            $bio = $raw !== null ? Bio::fromString($raw) : null;
        } else {
            $bio = $current?->bio();
        }

        $player->updateIdentity(PlayerIdentity::of(
            displayName: $displayName,
            bio: $bio,
            avatar: $current?->avatarUrl(),
        ));

        $this->playerRepository->save($player);
        $events = $player->pullDomainEvents();
        $this->eventDispatcher->dispatchEvents($events);

        return $player;
    }
}
