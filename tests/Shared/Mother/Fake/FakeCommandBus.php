<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Bus\CommandBusInterface;

final class FakeCommandBus implements CommandBusInterface
{
    private ?\Throwable $exception = null;

    private mixed $returnValue = null;

    public function willThrow(\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function willReturn(mixed $value): void
    {
        $this->returnValue = $value;
    }

    public function dispatch(object $command): mixed
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->returnValue;
    }
}
