<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Commands\InvitePlayerToMatch\InvitePlayerToMatchCommand;
use App\Features\Matches\Infrastructure\Http\v1\Requests\InvitePlayerToMatchRequest;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchInvitationResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class InvitePlayerToMatchController
{
    public function __construct(private CommandBusInterface $commandBus) {}

    public function __invoke(InvitePlayerToMatchRequest $request, string $id): JsonResponse
    {
        $invitation = $this->commandBus->dispatch(new InvitePlayerToMatchCommand(
            matchId: $id,
            inviterId: $request->user()->id,
            inviteeId: $request->string('invitee_id')->value(),
            team: $request->string('team')->value(),
            type: $request->string('type')->value(),
        ));

        return (new MatchInvitationResource($invitation))->response()->setStatusCode(201);
    }
}
