<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPlayerProfile;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;

final readonly class GetPlayerProfileQueryHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function __invoke(GetPlayerProfileQuery $query): Player
    {
        $userId = Id::fromString($query->userId);

        $player = $this->playerRepository->findById($userId);
        if (! $player) {
            throw PlayerProfileNotFoundException::create();
        }

        return $player;
    }
}
