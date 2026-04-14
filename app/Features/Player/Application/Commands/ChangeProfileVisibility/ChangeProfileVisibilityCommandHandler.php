<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\ChangeProfileVisibility;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\ProfileVisibility;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class ChangeProfileVisibilityCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function __invoke(ChangeProfileVisibilityCommand $command): Player
    {
        $userId = Id::fromString($command->userId);

        $player = $this->playerRepository->findById($userId);
        if (! $player) {
            throw PlayerProfileNotFoundException::create();
        }

        $player->changeVisibility(ProfileVisibility::fromBool($command->isPublic));

        $this->playerRepository->save($player);
        $events = $player->pullDomainEvents();
        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($events));

        return $player;
    }
}
