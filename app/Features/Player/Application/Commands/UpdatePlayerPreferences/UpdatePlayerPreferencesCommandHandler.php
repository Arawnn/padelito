<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerPreferences;

use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\DominantHand;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Location;
use App\Features\Player\Domain\ValueObjects\PlayerPreferences;
use App\Features\Player\Domain\ValueObjects\PreferredPosition;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class UpdatePlayerPreferencesCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(UpdatePlayerPreferencesCommand $command): Result
    {
        try {
            $userId = Id::fromString($command->userId);

            $player = $this->playerRepository->findById($userId);

            if (! $player) {
                return Result::fail(PlayerProfileNotFoundException::create());
            }

            $current = $player->preferences();

            if ($command->dominantHand->isPresent()) {
                $raw = $command->dominantHand->value();
                $dominantHand = $raw !== null
                    ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($raw))
                    : null;
            } else {
                $dominantHand = $current?->dominantHand();
            }

            if ($command->preferredPosition->isPresent()) {
                $raw = $command->preferredPosition->value();
                $preferredPosition = $raw !== null
                    ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($raw))
                    : null;
            } else {
                $preferredPosition = $current?->preferredPosition();
            }

            if ($command->location->isPresent()) {
                $raw = $command->location->value();
                $location = $raw !== null ? Location::fromString($raw) : null;
            } else {
                $location = $current?->location();
            }

            $player->updatePreferences(PlayerPreferences::of(
                dominantHand: $dominantHand,
                preferredPosition: $preferredPosition,
                location: $location,
            ));

            $this->playerRepository->save($player);
            $this->eventDispatcher->dispatchEvents($player->pullDomainEvents());

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
