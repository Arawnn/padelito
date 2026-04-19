<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommand;
use App\Features\Matches\Infrastructure\Http\v1\Requests\RespondToMatchInvitationRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class RespondToMatchInvitationController
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(RespondToMatchInvitationRequest $request, string $id, string $invId): JsonResponse
    {
        $this->commandBus->dispatch(new RespondToMatchInvitationCommand(
            invitationId: $invId,
            responderId: $request->user()->id,
            accept: (bool) $request->input('accept'),
        ));

        return response()->json(null, 204);
    }
}
