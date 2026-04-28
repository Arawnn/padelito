<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Queries\GetMatch\GetMatchQuery;
use App\Features\Matches\Application\ReadModels\MatchViewFactory;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetMatchController
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private MatchViewFactory $matchViewFactory,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $match = $this->queryBus->ask(new GetMatchQuery(matchId: $id));
        $view = $this->matchViewFactory->fromMatch($match, $request->user()?->id);

        return (new MatchResource($view))->response()->setStatusCode(200);
    }
}
