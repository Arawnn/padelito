<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Controllers;

use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommand;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogoutController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->commandBus->dispatch(new LogoutUserCommand(
            userId: $request->user()->id,
        ));

        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }
}
