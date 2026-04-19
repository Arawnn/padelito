<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UploadPlayerAvatar;

use App\Features\Player\Application\Dto\AvatarInput;

final readonly class UploadPlayerAvatarCommand
{
    public function __construct(
        public string $userId,
        public string $displayName,
        public ?AvatarInput $avatar,
    ) {}
}
