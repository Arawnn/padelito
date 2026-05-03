<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommand;
use App\Features\Matches\Application\QueryResults\MatchQueryResultFactory;
use App\Features\Matches\Infrastructure\Http\v1\Requests\UpdateMatchRequest;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class UpdateMatchController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private MatchQueryResultFactory $matchQueryResultFactory,
    ) {}

    public function __invoke(UpdateMatchRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $match = $this->commandBus->dispatch(new UpdateMatchCommand(
            matchId: $id,
            requesterId: $request->user()->id,
            courtName: $validated['court_name'] ?? null,
            matchDate: $validated['match_date'] ?? null,
            notes: $validated['notes'] ?? null,
            matchFormat: $validated['match_format'] ?? null,
            matchType: $validated['match_type'] ?? null,
            setsDetail: $validated['sets_detail'] ?? null,
            setsToWin: array_key_exists('sets_to_win', $validated) ? (int) $validated['sets_to_win'] : null,
            fields: $this->providedFields($validated),
        ));
        $view = $this->matchQueryResultFactory->detailsFromMatch($match, $request->user()->id);

        return (new MatchResource($view))->response()->setStatusCode(200);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<string>
     */
    private function providedFields(array $validated): array
    {
        $fields = [];
        $map = [
            'court_name' => 'courtName',
            'match_date' => 'matchDate',
            'notes' => 'notes',
            'match_format' => 'matchFormat',
            'match_type' => 'matchType',
            'sets_detail' => 'setsDetail',
            'sets_to_win' => 'setsToWin',
        ];

        foreach ($map as $requestField => $commandField) {
            if (array_key_exists($requestField, $validated)) {
                $fields[] = $commandField;
            }
        }

        return $fields;
    }
}
