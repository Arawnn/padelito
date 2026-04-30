<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\CancelMatch\CancelMatchCommand;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class CancelMatchController
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $this->commandBus->dispatch(new CancelMatchCommand(
            matchId: $id,
            requesterId: $request->user()->id,
        ));

        return response()->json(null, 204);
    }
}
