<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Dto;

final readonly class AvatarInput
{
    public function __construct(
        public ?string $uploadedFilePath,
        public ?string $uploadedFileExtension,
        public ?string $remoteUrl,
    ) {}

    public function hasUploadedFile(): bool
    {
        return $this->uploadedFilePath !== null;
    }

    public function hasRemoteUrl(): bool
    {
        return $this->remoteUrl !== null && $this->remoteUrl !== '';
    }
}
