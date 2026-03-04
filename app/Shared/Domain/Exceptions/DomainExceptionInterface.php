<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

interface DomainExceptionInterface
{
    public function getDomainCode(): string;

    public function getMessage(): string;

    public function getMeta(): array;
}
