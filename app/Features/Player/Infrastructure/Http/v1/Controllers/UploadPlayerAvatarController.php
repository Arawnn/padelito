<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Controllers;

use App\Features\Player\Application\Commands\UploadPlayerAvatar\UploadPlayerAvatarCommand;
use App\Features\Player\Application\Dto\AvatarInput;
use App\Features\Player\Infrastructure\Http\v1\Requests\UploadPlayerAvatarRequest;
use App\Features\Player\Infrastructure\Http\v1\Resources\PlayerProfileResource;
use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Http\JsonResponse;

final readonly class UploadPlayerAvatarController
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UploadPlayerAvatarRequest $request): JsonResponse
    {
        $file = $request->file('avatar');
        $remoteUrl = $request->string('avatar')->value() !== '' ? $request->string('avatar')->value() : null;

        $filePath = null;
        if ($file !== null) {
            $realPath = $file->getRealPath();
            $filePath = $realPath !== false ? $realPath : null;
        }

        $avatar = ($file !== null || $remoteUrl !== null)
            ? new AvatarInput(
                uploadedFilePath: $filePath,
                uploadedFileExtension: $file?->getClientOriginalExtension(),
                remoteUrl: $remoteUrl,
            )
            : null;

        $player = $this->commandBus->dispatch(new UploadPlayerAvatarCommand(
            userId: $request->user()->id,
            avatar: $avatar,
        ));

        return (new PlayerProfileResource($player))
            ->response()
            ->setStatusCode(200);
    }
}
