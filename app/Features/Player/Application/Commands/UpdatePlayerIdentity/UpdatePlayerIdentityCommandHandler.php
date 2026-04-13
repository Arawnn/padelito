<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerIdentity;

use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use App\Features\Player\Domain\ValueObjects\Bio;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Shared\Application\Result;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class UpdatePlayerIdentityCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private AvatarProvisionerInterface $avatarProvisioner,
        private EventDispatcherInterface $eventDispatcher,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function __invoke(UpdatePlayerIdentityCommand $command): Result
    {
        try {
            $userId = Id::fromString($command->userId);

            $player = $this->playerRepository->findById($userId);

            if (! $player) {
                return Result::fail(PlayerProfileNotFoundException::create());
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

            $avatarUrl = $current?->avatarUrl()?->value();

            if ($command->avatar !== null) {
                $avatarResult = $this->avatarProvisioner->provision(
                    userId: $command->userId,
                    displayName: $command->displayName->value() ?? '',
                    avatar: $command->avatar,
                );

                if ($avatarResult->isFail()) {
                    return Result::fail($avatarResult->error());
                }

                $oldAvatarUrl = $avatarUrl;
                $avatarUrl = $avatarResult->value();

                if ($oldAvatarUrl !== null) {
                    $this->avatarProvisioner->deleteByPublicUrl($oldAvatarUrl);
                }
            }

            $player->updateIdentity(PlayerIdentity::of(
                displayName: $displayName,
                bio: $bio,
                avatar: $avatarUrl !== null ? AvatarUrl::fromString($avatarUrl) : null,
            ));

            $this->playerRepository->save($player);
            $events = $player->pullDomainEvents();
            $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($events));

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
