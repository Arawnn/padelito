<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class InvalidCourtNameException extends DomainException
{
    /** @param array<int, string> $violations */
    private function __construct(private readonly array $violations)
    {
        parent::__construct(
            implode(', ', $violations),
            domainCode: 'INVALID_COURT_NAME',
            meta: ['violations' => $violations]
        );
    }

    /** @param array<int, string> $violations */
    public static function fromViolations(array $violations): self
    {
        return new self($violations);
    }

    protected function getDefaultCode(): string
    {
        return 'INVALID_COURT_NAME';
    }
}
