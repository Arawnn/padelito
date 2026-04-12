<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\CreatePlayerProfile\Contracts;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;
use App\Shared\Application\Result;

interface AvatarProvisionerInterface
{
    /** @return Result<?string> */
    public function provision(
        string $userId,
        string $displayName,
        ?AvatarInput $avatar,
    ): Result;

    public function deleteByPublicUrl(string $publicUrl): void;
}