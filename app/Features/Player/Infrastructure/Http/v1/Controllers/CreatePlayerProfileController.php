<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\CreatePlayerProfileCommand;
use App\Features\Player\Infrastructure\Dto\CreatePlayerProfileAvatarInput;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Http\v1\Requests\CreatePlayerProfileRequest;
use App\Features\Player\Infrastructure\Services\ResolvePlayerProfileAvatarService;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Domain\Contracts\FileStorageInterface;
use Illuminate\Http\JsonResponse;

class CreatePlayerProfileController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ResolvePlayerProfileAvatarService $resolveAvatar,
        private FileStorageInterface $fileStorage,
    ) {}

    public function __invoke(CreatePlayerProfileRequest $request): JsonResponse
    {
        $avatarResult = $this->resolveAvatar->resolve(
            new CreatePlayerProfileAvatarInput(
                userId: $request->user()->id,
                displayName: $request->displayName,
                avatarFile: $request->hasFile('avatar') ? $request->file('avatar') : null,
                avatarAsHttpsUrlOrEmpty: $request->string('avatar')->value(),
            )
        );
        if ($avatarResult->isFail()) {
            return PlayerExceptionMapper::toResponse($avatarResult->error());
        }

        $uploadedPublicUrl = $avatarResult->value();

        $result = $this->commandBus->dispatch(
            new CreatePlayerProfileCommand(
                userId: $request->user()->id,
                username: $request->username,
                level: $request->level,
                displayName: $request->displayName,
                avatarUrl: $uploadedPublicUrl,
                bio: $request->bio,
                location: $request->location,
                dominantHand: $request->dominantHand,
                preferredPosition: $request->preferredPosition
            )
        );

        if ($result->isFail()) {
            if ($uploadedPublicUrl !== null) {
                $this->fileStorage->delete($uploadedPublicUrl);
            }

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
