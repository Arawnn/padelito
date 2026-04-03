<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class PlayerIdentity
{
    private function __construct(
        private readonly ?DisplayName $displayName,
        private readonly ?Bio $bio,
        private readonly ?AvatarUrl $avatarUrl
    ) {}

    public static function of(?DisplayName $displayName, ?Bio $bio, ?AvatarUrl $avatar): self
    {
        return new self(
            displayName: $displayName,
            bio: $bio,
            avatarUrl: $avatar
        );
    }
}
