<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Domain\Contracts\UuidGeneratorInterface;

final class FakeUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return '00000000-0000-0000-0000-000000000000';
    }
}
