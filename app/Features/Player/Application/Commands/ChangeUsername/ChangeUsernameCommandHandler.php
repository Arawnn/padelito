<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\ChangeUsername;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Exceptions\UsernameAlreadyTakenException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class ChangeUsernameCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function __invoke(ChangeUsernameCommand $command): Player
    {
        $userId = Id::fromString($command->userId);
        $newUsername = Username::fromString($command->newUsername);

        $player = $this->playerRepository->findById($userId);
        if (! $player) {
            throw PlayerProfileNotFoundException::create();
        }

        if ($player->username()->value() === $newUsername->value()) {
            return $player;
        }

        if ($this->playerRepository->findByUsername($newUsername) !== null) {
            throw UsernameAlreadyTakenException::create();
        }

        $player->changeUsername($newUsername);

        $this->playerRepository->save($player);
        $events = $player->pullDomainEvents();
        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($events));

        return $player;
    }
}
