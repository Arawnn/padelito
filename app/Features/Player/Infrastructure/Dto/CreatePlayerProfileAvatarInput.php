<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Dto;

use Illuminate\Http\UploadedFile;

final readonly class CreatePlayerProfileAvatarInput
{
    public function __construct(
        public string $userId,
        public string $displayName,
        public ?UploadedFile $avatarFile,
        public string $avatarAsHttpsUrlOrEmpty,
    ) {}
}
