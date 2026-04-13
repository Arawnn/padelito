<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQuery;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Http\v1\Resources\PublicPlayerProfileResource;
use App\Shared\Application\Bus\QueryBusInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class GetPublicPlayerProfileController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(Request $request, string $username): JsonResponse
    {
        $result = $this->queryBus->ask(new GetPublicPlayerProfileQuery(
            targetUsername: $username,
        ));

        if ($result->isFail()) {
            return PlayerExceptionMapper::toResponse($result->error());
        }

        return (new PublicPlayerProfileResource($result->value()))
            ->response()
            ->setStatusCode(200);
    }
}
