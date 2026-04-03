<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

final class InvalidUuidException extends DomainException
{
    /**
     * @param  array<int, string>  $violations
     */
    private function __construct(
        private readonly array $violations
    ) {
        parent::__construct(
            implode(', ', $violations),
            domainCode: 'INVALID_UUID',
            meta: ['violations' => $violations]
        );
    }

    /**
     * @param  array<int, string>  $violations
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
        return 'INVALID_UUID';
    }
}
