<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Helpers;

use App\Shared\Domain\Contracts\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

final readonly class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
