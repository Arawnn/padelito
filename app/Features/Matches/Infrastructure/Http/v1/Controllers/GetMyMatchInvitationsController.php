<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Controllers;

use App\Features\Matches\Application\Queries\GetMyMatchInvitations\GetMyMatchInvitationsQuery;
use App\Features\Matches\Infrastructure\Http\v1\Resources\MatchInvitationResource;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetMyMatchInvitationsController
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function __invoke(Request $request): JsonResponse
    {
        $invitations = $this->queryBus->ask(new GetMyMatchInvitationsQuery(
            playerId: $request->user()->id,
        ));

        return MatchInvitationResource::collection($invitations)->response()->setStatusCode(200);
    }
}
