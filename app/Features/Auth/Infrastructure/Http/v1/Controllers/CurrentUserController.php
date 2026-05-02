<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Queries\GetCurrentUser\GetCurrentUserQuery;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CurrentUserController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->queryBus->ask(new GetCurrentUserQuery(
            userId: (string) $request->user()->getAuthIdentifier(),
        ));

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id()->value(),
                    'name' => $user->name()->value(),
                    'email' => $user->email()->value(),
                ],
            ],
        ]);
    }
}
