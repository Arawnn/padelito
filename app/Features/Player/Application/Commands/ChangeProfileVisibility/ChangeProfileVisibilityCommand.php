<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\ChangeProfileVisibility;

final readonly class ChangeProfileVisibilityCommand
{
    public function __construct(
        public string $userId,
        public bool $isPublic,
    ) {}
}
