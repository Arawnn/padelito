<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\DominantHandEnum;
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
        private TransactionManagerInterface $tx,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * TODO: return a DTO instead of exposing the aggregate root
     *
     * @return Result<Player>
     *
     * @throws DomainExceptionInterface
     */
    public function __invoke(CreatePlayerProfileCommand $command): Result
    {
        try {
            $userId = Id::fromString($command->userId);

            if ($this->playerRepository->findById($userId)) {
                return Result::fail(PlayerProfileAlreadyExistException::fromUserId($command->userId));
            }

            if ($this->playerRepository->findByUsername(Username::fromString($command->username))) {
                return Result::fail(PlayerProfileAlreadyExistException::fromUsername($command->username));
            }

            $playerProfile = $this->tx->run(fn () => $this->buildProfile($command, $userId));

            return Result::ok($playerProfile);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }

    private function buildProfile(CreatePlayerProfileCommand $command, Id $userId): Player
    {
        $preferences = PlayerPreferences::of(
            dominantHand: $command->dominantHand ? DominantHand::fromDominantHandEnum(DominantHandEnum::tryFrom($command->dominantHand)) : null,
            preferredPosition: $command->preferredPosition ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::tryFrom($command->preferredPosition)) : null,
            location: $command->location ? Location::fromString($command->location) : null,
        );

        $identity = PlayerIdentity::of(
            displayName: $command->displayName ? DisplayName::fromString($command->displayName) : null,
            bio: $command->bio ? Bio::fromString($command->bio) : null,
            avatar: $command->avatarUrl ? AvatarUrl::fromString($command->avatarUrl) : null,
        );

        $playerProfile = Player::create(
            id: $userId,
            username: Username::fromString($command->username),
            preferences: $preferences,
            identity: $identity,
            stats: PlayerStats::initialize(),
            padelCoins: PadelCoins::initialize(),
        );

        $this->playerRepository->save($playerProfile);

        $domainEvents = $playerProfile->pullDomainEvents();
        $this->tx->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($domainEvents));

        return $playerProfile;
    }
}
