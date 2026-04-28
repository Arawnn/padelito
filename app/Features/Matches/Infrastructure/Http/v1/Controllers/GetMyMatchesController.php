<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Queries\GetMyMatches\GetMyMatchesQuery;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Features\Matches\Infrastructure\Http\v1\ViewModels\MatchViewFactory;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetMyMatchesController
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private MatchViewFactory $matchViewFactory,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $matches = $this->queryBus->ask(new GetMyMatchesQuery(
            playerId: $request->user()->id,
            filter: $request->query('filter'),
        ));
        $views = $this->matchViewFactory->fromMatches($matches, $request->user()->id);

        return MatchResource::collection($views)->response()->setStatusCode(200);
    }
}
