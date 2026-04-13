<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQuery;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetPlayerProfileController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {

        $result = $this->queryBus->ask(new GetPlayerProfileQuery(
            userId: $request->user()->id,
        ));

        if ($result->isFail()) {
            return PlayerExceptionMapper::toResponse($result->error());
        }

        return (new PlayerProfileResource($result->value()))
            ->response()
            ->setStatusCode(200);
    }
}
