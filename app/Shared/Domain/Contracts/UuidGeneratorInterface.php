<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
