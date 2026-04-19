<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Infrastructure\Http\v1\Requests\CreatePlayerProfileRequest;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class CreatePlayerProfileController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(CreatePlayerProfileRequest $request): JsonResponse
    {
        $player = $this->commandBus->dispatch(
            new CreatePlayerProfileCommand(
                userId: $request->user()->id,
                username: $request->username,
                level: $request->level,
                displayName: $request->displayName,
                bio: $request->bio,
                location: $request->location,
                dominantHand: $request->dominantHand,
                preferredPosition: $request->preferredPosition,
            )
        );

        return response()->json([
            'data' => [
                'player' => [
                    'id' => $player->id()->value(),
                ],
            ],
            'message' => 'Player profile created successfully',
        ], 201);
    }
}
