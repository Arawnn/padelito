<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

interface DomainExceptionInterface extends \Throwable
{
    public function getDomainCode(): string;

    public function getMeta(): array;
}
