<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommand;
use App\Features\Matches\Application\ReadModels\MatchReadModelFactory;
use App\Features\Matches\Infrastructure\Http\v1\Requests\UpdateMatchRequest;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class UpdateMatchController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private MatchReadModelFactory $matchReadModelFactory,
    ) {}

    public function __invoke(UpdateMatchRequest $request, string $id): JsonResponse
    {
        $match = $this->commandBus->dispatch(new UpdateMatchCommand(
            matchId: $id,
            requesterId: $request->user()->id,
            courtName: $request->input('court_name'),
            matchDate: $request->input('match_date'),
            notes: $request->input('notes'),
            matchFormat: $request->input('match_format'),
            matchType: $request->input('match_type'),
            setsDetail: $request->input('sets_detail'),
            setsToWin: $request->input('sets_to_win') !== null ? (int) $request->input('sets_to_win') : null,
        ));
        $view = $this->matchReadModelFactory->detailsFromMatch($match, $request->user()->id);

        return (new MatchResource($view))->response()->setStatusCode(200);
    }
}
