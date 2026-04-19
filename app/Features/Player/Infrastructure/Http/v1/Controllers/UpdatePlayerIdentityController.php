<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Infrastructure\Http\v1\Requests\UpdatePlayerIdentityRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Optional;
use Illuminate\Http\JsonResponse;

final readonly class UpdatePlayerIdentityController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UpdatePlayerIdentityRequest $request): JsonResponse
    {
        $displayName = $request->has('displayName')
            ? Optional::of($request->input('displayName'))
            : Optional::absent();
        $bio = $request->has('bio')
            ? Optional::of($request->input('bio'))
            : Optional::absent();

        $player = $this->commandBus->dispatch(new UpdatePlayerIdentityCommand(
            userId: $request->user()->id,
            displayName: $displayName,
            bio: $bio,
        ));

        return (new PlayerProfileResource($player))
            ->response()
            ->setStatusCode(200);
    }
}
