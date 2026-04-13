<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\ChangeUsername;

use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Exceptions\UsernameAlreadyTakenException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class ChangeUsernameCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ChangeUsernameCommand $command): Result
    {
        try {
            $userId = Id::fromString($command->userId);
            $newUsername = Username::fromString($command->newUsername);

            $player = $this->playerRepository->findById($userId);

            if (! $player) {
                return Result::fail(PlayerProfileNotFoundException::create());
            }

            if ($player->username()->value() === $newUsername->value()) {
                return Result::ok($player);
            }

            $existing = $this->playerRepository->findByUsername($newUsername);

            if ($existing !== null) {
                return Result::fail(UsernameAlreadyTakenException::create());
            }

            $player->changeUsername($newUsername);

            $this->playerRepository->save($player);
            $this->eventDispatcher->dispatchEvents($player->pullDomainEvents());

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
