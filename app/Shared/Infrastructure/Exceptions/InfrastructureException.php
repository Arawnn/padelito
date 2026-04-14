<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Exceptions;

final class InfrastructureException extends \RuntimeException
{
    private function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function storageFailed(string $operation, string $driver, ?\Throwable $previous = null): self
    {
        return new self(
            "Failed to upload file to storage ({$operation} on {$driver} disk).",
            $previous
        );
    }

    public static function handlerNotFound(string $commandClass): self
    {
        return new self("No handler registered for {$commandClass}");
    }
}
