<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommand;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ConfirmMatchController
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $this->commandBus->dispatch(new ConfirmMatchCommand(
            matchId: $id,
            playerId: $request->user()->id,
        ));

        return response()->json(null, 204);
    }
}
