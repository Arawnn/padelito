<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommand;
use App\Features\Player\Infrastructure\Http\v1\Requests\UpdatePlayerPreferencesRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Optional;
use Illuminate\Http\JsonResponse;

final readonly class UpdatePlayerPreferencesController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UpdatePlayerPreferencesRequest $request): JsonResponse
    {
        $dominantHand = $request->has('dominantHand')
            ? Optional::of($request->input('dominantHand'))
            : Optional::absent();
        $preferredPosition = $request->has('preferredPosition')
            ? Optional::of($request->input('preferredPosition'))
            : Optional::absent();
        $location = $request->has('location')
            ? Optional::of($request->input('location'))
            : Optional::absent();

        $player = $this->commandBus->dispatch(new UpdatePlayerPreferencesCommand(
            userId: $request->user()->id,
            dominantHand: $dominantHand,
            preferredPosition: $preferredPosition,
            location: $location,
        ));

        return (new PlayerProfileResource($player))
            ->response()
            ->setStatusCode(200);
    }
}
