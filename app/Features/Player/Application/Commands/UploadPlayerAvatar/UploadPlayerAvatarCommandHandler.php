<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UploadPlayerAvatar;

use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class UploadPlayerAvatarCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private AvatarProvisionerInterface $avatarProvisioner,
        private EventDispatcherInterface $eventDispatcher,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function __invoke(UploadPlayerAvatarCommand $command): Player
    {
        $userId = Id::fromString($command->userId);

        $player = $this->playerRepository->findById($userId);
        if (! $player) {
            throw PlayerProfileNotFoundException::create();
        }

        $current = $player->identity();
        $oldAvatarUrl = $current?->avatarUrl()?->value();

        $newAvatarUrl = $this->avatarProvisioner->provision(
            userId: $command->userId,
            displayName: $command->displayName,
            avatar: $command->avatar,
        );

        $player->updateIdentity(PlayerIdentity::of(
            displayName: $current?->displayName(),
            bio: $current?->bio(),
            avatar: $newAvatarUrl !== null ? AvatarUrl::fromString($newAvatarUrl) : null,
        ));

        $this->playerRepository->save($player);

        if ($oldAvatarUrl !== null) {
            $this->avatarProvisioner->deleteByPublicUrl($oldAvatarUrl);
        }

        $events = $player->pullDomainEvents();
        $this->transactionManager->afterCommit(
            fn () => $this->eventDispatcher->dispatchEvents($events)
        );

        return $player;
    }
}
