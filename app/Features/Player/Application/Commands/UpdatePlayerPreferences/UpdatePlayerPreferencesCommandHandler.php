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
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class UpdatePlayerPreferencesCommandHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function __invoke(UpdatePlayerPreferencesCommand $command): Result
    {
        try {
            $userId = Id::fromString($command->userId);

            $player = $this->playerRepository->findById($userId);

            if (! $player) {
                return Result::fail(PlayerProfileNotFoundException::create());
            }

            $player->updatePreferences(PlayerPreferences::of(
                dominantHand: $command->dominantHand !== null
                    ? DominantHand::fromDominantHandEnum(DominantHandEnum::from($command->dominantHand))
                    : null,
                preferredPosition: $command->preferredPosition !== null
                    ? PreferredPosition::fromPreferredPositionEnum(PreferredPositionEnum::from($command->preferredPosition))
                    : null,
                location: $command->location !== null ? Location::fromString($command->location) : null,
            ));

            $this->playerRepository->save($player);

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
