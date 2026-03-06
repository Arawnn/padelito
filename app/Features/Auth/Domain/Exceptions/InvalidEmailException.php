<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class InvalidEmailException extends DomainException
{
    /**
     * @param array<int, string> $violations
     */
    private function __construct(
        private readonly array $violations
    ) {
        parent::__construct(
            implode(', ', $violations),
            domainCode: 'INVALID_EMAIL',
            meta: ['violations' => $violations]
        );
    }

    /**
     * @param array<int, string> $violations
     */
    public static function fromViolations(array $violations): self
    {
        return new self($violations);
    }

    /**
     * @return array<int, string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    protected function getDefaultCode(): string
    {
        return 'INVALID_EMAIL';
    }
}
