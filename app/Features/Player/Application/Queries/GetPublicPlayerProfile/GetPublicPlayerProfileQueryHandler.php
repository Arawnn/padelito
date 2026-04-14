<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPublicPlayerProfile;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Username;

final readonly class GetPublicPlayerProfileQueryHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function __invoke(GetPublicPlayerProfileQuery $query): Player
    {
        $player = $this->playerRepository->findByUsername(
            Username::fromString($query->targetUsername),
        );

        if (! $player || $player->visibility()->isPrivate()) {
            throw PlayerProfileNotFoundException::create();
        }

        return $player;
    }
}
