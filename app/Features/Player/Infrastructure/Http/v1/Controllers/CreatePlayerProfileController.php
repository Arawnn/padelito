<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Dto\AvatarInput;
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
        $file = $request->file('avatar');
        $remoteUrl = $request->string('avatar')->value() !== '' ? $request->string('avatar')->value() : null;
        $realPath = $file?->getRealPath();
        $uploadedFilePath = ($realPath !== false && $realPath !== null) ? $realPath : null;

        $avatar = ($file !== null || $remoteUrl !== null)
            ? new AvatarInput(
                uploadedFilePath: $uploadedFilePath,
                uploadedFileExtension: $file?->getClientOriginalExtension(),
                remoteUrl: $remoteUrl,
            )
            : null;

        $player = $this->commandBus->dispatch(
            new CreatePlayerProfileCommand(
                userId: $request->user()->id,
                username: $request->username,
                level: $request->level,
                displayName: $request->displayName,
                avatar: $avatar,
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
