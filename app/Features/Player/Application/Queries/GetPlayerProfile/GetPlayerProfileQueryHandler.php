<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPlayerProfile;

use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Shared\Application\Result;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class GetPlayerProfileQueryHandler
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function __invoke(GetPlayerProfileQuery $query): Result
    {
        try {
            $userId = Id::fromString($query->userId);

            $player = $this->playerRepository->findById($userId);

            if (! $player) {
                return Result::fail(PlayerProfileNotFoundException::create());
            }

            return Result::ok($player);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
