<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
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

        $result = $this->commandBus->dispatch(
            new CreatePlayerProfileCommand(
                userId: $request->user()->id,
                username: $request->username,
                level: $request->level,
                displayName: $request->displayName,
                avatar: ($file !== null || $request->string('avatar')->value() !== '')
                    ? new AvatarInput(
                        uploadedFilePath: $file?->getRealPath() ?: null,
                        uploadedFileExtension: $file?->getClientOriginalExtension(),
                        remoteUrl: $request->string('avatar')->value() !== '' ? $request->string('avatar')->value() : null,
                    )
                    : null,
                bio: $request->bio,
                location: $request->location,
                dominantHand: $request->dominantHand,
                preferredPosition: $request->preferredPosition,
            )
        );

        if ($result->isFail()) {
            return PlayerExceptionMapper::toResponse($result->error());
        }

        $player = $result->value();

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
