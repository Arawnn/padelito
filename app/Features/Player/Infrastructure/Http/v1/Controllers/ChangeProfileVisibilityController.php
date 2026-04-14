<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommand;
use App\Features\Player\Infrastructure\Http\v1\Requests\ChangeProfileVisibilityRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class ChangeProfileVisibilityController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
        // Named constructors only.
    }

    public function __invoke(ChangeProfileVisibilityRequest $request): JsonResponse
    {
        $player = $this->commandBus->dispatch(new ChangeProfileVisibilityCommand(
            userId: $request->user()->id,
            isPublic: (bool) $request->input('is_public'),
        ));

        return (new PlayerProfileResource($player))
            ->response()
            ->setStatusCode(200);
    }
}
