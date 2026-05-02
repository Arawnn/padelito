<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Queries\GetMatch\GetMatchQuery;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetMatchController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $match = $this->queryBus->ask(new GetMatchQuery(matchId: $id, currentUserId: $request->user()?->id));

        return (new MatchResource($match))->response()->setStatusCode(200);
    }
}
