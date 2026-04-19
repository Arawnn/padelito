<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Contracts;

use App\Features\Player\Application\Dto\AvatarInput;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

interface AvatarProvisionerInterface
{
    /** @throws DomainExceptionInterface */
    public function provision(
        string $userId,
        ?string $displayName,
        ?AvatarInput $avatar,
    ): ?string;

    public function deleteByPublicUrl(string $publicUrl): void;
}
