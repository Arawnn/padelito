<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\CreatePlayerProfile;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
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
use App\Shared\Application\Result;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class CreatePlayerProfileCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
        private AvatarProvisionerInterface $avatarProvisioner,
    ) {}

    public function __invoke(CreatePlayerProfileCommand $command): Result
    {
        $avatarUrl = null;

        try {
            $userId = Id::fromString($command->userId);

            if ($this->playerRepository->findById($userId)) {
                return Result::fail(PlayerProfileAlreadyExistException::create());
            }

            if ($this->playerRepository->findByUsername(Username::fromString($command->username))) {
                return Result::fail(PlayerProfileAlreadyExistException::create());
            }

            $avatarResult = $this->avatarProvisioner->provision(
                userId: $command->userId,
                displayName: $command->displayName ?? '',
                avatar: $command->avatar,
            );

            if ($avatarResult->isFail()) {
                return Result::fail($avatarResult->error());
            }

            $avatarUrl = $avatarResult->value();

            $playerProfile = $this->transactionManager->run(
                fn () => $this->buildProfile($command, $userId, $avatarUrl)
            );

            return Result::ok($playerProfile);
        } catch (DomainExceptionInterface $e) {
            $this->deleteAvatar($avatarUrl);
            return Result::fail($e);
        } catch (\Throwable $e) {
            $this->deleteAvatar($avatarUrl);
            throw $e;
        }
    }

    private function deleteAvatar(?string $avatarUrl): void
    {
        if ($avatarUrl !== null) {
            $this->avatarProvisioner->deleteByPublicUrl($avatarUrl);
        }
    }

    private function buildProfile(CreatePlayerProfileCommand $command, Id $userId, ?string $avatarUrl): Player
    {
        $preferences = PlayerPreferences::of(
            dominantHand: $command->dominantHand ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($command->dominantHand)) : null,
            preferredPosition: $command->preferredPosition ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($command->preferredPosition)) : null,
            location: $command->location ? Location::fromString($command->location) : null,
        );

        $identity = PlayerIdentity::of(
            displayName: $command->displayName ? DisplayName::fromString($command->displayName) : null,
            bio: $command->bio ? Bio::fromString($command->bio) : null,
            avatar: $avatarUrl ? AvatarUrl::fromString($avatarUrl) : null,
        );

        $playerProfile = Player::create(
            id: $userId,
            username: Username::fromString($command->username),
            level: PlayerLevel::fromPlayerLevelEnum(PlayerLevelEnum::from($command->level)),
            preferences: $preferences,
            identity: $identity,
            stats: PlayerStats::initialize(),
            padelCoins: PadelCoins::initialize(),
        );

        $this->playerRepository->save($playerProfile);

        $domainEvents = $playerProfile->pullDomainEvents();
        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($domainEvents));

        return $playerProfile;
    }
}
