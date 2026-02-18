<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Helpers;

use Ramsey\Uuid\Uuid;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;

final readonly class UuidGenerator implements UuidGeneratorInterface {
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}