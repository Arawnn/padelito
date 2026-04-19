<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Bus\CommandBusInterface;

final class SpyCommandBus implements CommandBusInterface
{
    /** @var list<object> */
    private array $dispatched = [];

    private mixed $returnValue = null;

    public function withReturnValue(mixed $value): self
    {
        $this->returnValue = $value;

        return $this;
    }

    public function dispatch(object $command): mixed
    {
        $this->dispatched[] = $command;

        return $this->returnValue;
    }

    public function dispatchedOfType(string $class): ?object
    {
        foreach ($this->dispatched as $command) {
            if ($command instanceof $class) {
                return $command;
            }
        }

        return null;
    }

    public function wasDispatched(string $class): bool
    {
        return $this->dispatchedOfType($class) !== null;
    }
}
