<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\CreateMatch\CreateMatchCommand;
use App\Features\Matches\Application\QueryResults\MatchQueryResultFactory;
use App\Features\Matches\Infrastructure\Http\v1\Requests\CreateMatchRequest;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class CreateMatchController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private MatchQueryResultFactory $matchQueryResultFactory,
    ) {}

    public function __invoke(CreateMatchRequest $request): JsonResponse
    {
        $match = $this->commandBus->dispatch(new CreateMatchCommand(
            creatorId: $request->user()->id,
            matchType: $request->string('match_type')->value(),
            matchFormat: $request->string('match_format')->value(),
            courtName: $request->input('court_name'),
            matchDate: $request->input('match_date'),
            notes: $request->input('notes'),
            setsToWin: $request->input('sets_to_win') !== null ? (int) $request->input('sets_to_win') : null,
        ));
        $view = $this->matchQueryResultFactory->detailsFromMatch($match, $request->user()->id);

        return (new MatchResource($view))->response()->setStatusCode(201);
    }
}
