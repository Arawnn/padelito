<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\ChangeUsername\ChangeUsernameCommand;
use App\Features\Player\Infrastructure\Http\v1\Requests\ChangeUsernameRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class ChangeUsernameController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(ChangeUsernameRequest $request): JsonResponse
    {
        $player = $this->commandBus->dispatch(new ChangeUsernameCommand(
            userId: $request->user()->id,
            newUsername: $request->input('username'),
        ));

        return (new PlayerProfileResource($player))
            ->response()
            ->setStatusCode(200);
    }
}
