<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

abstract class DomainException extends \Exception implements DomainExceptionInterface
{
    public function __construct(
        string $message = '',
        protected string $domainCode = '',
        protected array $meta = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getDomainCode(): string
    {
        return $this->domainCode ?: $this->getDefaultCode();
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    abstract protected function getDefaultCode(): string;
}
