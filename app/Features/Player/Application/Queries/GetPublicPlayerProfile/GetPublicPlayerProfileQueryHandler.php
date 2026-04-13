<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPublicPlayerProfile;

use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Shared\Application\Result;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class GetPublicPlayerProfileQueryHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function __invoke(GetPublicPlayerProfileQuery $query): Result
    {
        try {
            $player = $this->playerRepository->findByUsername(
                Username::fromString($query->targetUsername),
            );

            if (! $player || $player->visibility()->isPrivate()) {
                return Result::fail(PlayerProfileNotFoundException::create());
            }

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
