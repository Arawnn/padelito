<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommand;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Http\v1\Requests\UpdatePlayerPreferencesRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class UpdatePlayerPreferencesController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UpdatePlayerPreferencesRequest $request): JsonResponse
    {
        $result = $this->commandBus->dispatch(new UpdatePlayerPreferencesCommand(
            userId: $request->user()->id,
            dominantHand: $request->input('dominantHand'),
            preferredPosition: $request->input('preferredPosition'),
            location: $request->input('location'),
        ));

        if ($result->isFail()) {
            return PlayerExceptionMapper::toResponse($result->error());
        }

        return (new PlayerProfileResource($result->value()))
            ->response()
            ->setStatusCode(200);
    }
}
