<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Http\v1\Requests\UpdatePlayerIdentityRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class UpdatePlayerIdentityController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UpdatePlayerIdentityRequest $request): JsonResponse
    {
        $file = $request->file('avatar');

        $avatar = ($file !== null || $request->string('avatar')->value() !== '')
            ? new AvatarInput(
                uploadedFilePath: $file?->getRealPath() ?: null,
                uploadedFileExtension: $file?->getClientOriginalExtension(),
                remoteUrl: $request->string('avatar')->value() !== '' ? $request->string('avatar')->value() : null,
            )
            : null;

        $result = $this->commandBus->dispatch(new UpdatePlayerIdentityCommand(
            userId: $request->user()->id,
            displayName: $request->input('displayName'),
            bio: $request->input('bio'),
            avatar: $avatar,
        ));

        if ($result->isFail()) {
            return PlayerExceptionMapper::toResponse($result->error());
        }

        return (new PlayerProfileResource($result->value()))
            ->response()
            ->setStatusCode(200);
    }
}
