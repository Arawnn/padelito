<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\InitializePlayerProfile;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Exceptions\PlayerProfileAlreadyExistException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\Services\UsernameGeneratorService;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\PadelCoins;
use App\Features\Player\Domain\ValueObjects\PlayerIdentity;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PlayerStats;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

/**
 * Bootstrap initial d'un profil player lors de la création d'un compte.
 * Ne sert ni à recréer ni à mettre à jour un joueur existant.
 * Transaction root : appelé par (RegisterPlayerCommandHandler).
 */
final readonly class InitializePlayerProfileCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private UsernameGeneratorService $usernameGenerator,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(InitializePlayerProfileCommand $command): void
    {
        $userId = Id::fromString($command->userId);

        if ($this->playerRepository->findById($userId)) {
            throw PlayerProfileAlreadyExistException::create();
        }

        $username = $this->usernameGenerator->generateFrom($command->displayName);

        $player = Player::create(
            id: $userId,
            username: $username,
            preferences: PlayerPreferences::of(
                dominantHand: null,
                preferredPosition: null,
                location: null,
            ),
            identity: PlayerIdentity::of(
                displayName: DisplayName::fromString($command->displayName),
                bio: null,
                avatar: null,
            ),
            stats: PlayerStats::initialize(),
            level: PlayerLevel::fromPlayerLevelEnum(PlayerLevelEnum::BEGINNER),
            padelCoins: PadelCoins::initialize(),
        );

        $this->playerRepository->save($player);

        $domainEvents = $player->pullDomainEvents();
        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($domainEvents));
    }
}
