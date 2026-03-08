<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Domain\ValueObjects\Result;

final readonly class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private HandlerMap $handlers
    ) {}

    public function dispatch(object $command): Result
    {
        $handlerClass = $this->handlers->handlerFor($command);
        $handler = app($handlerClass);

        return $handler($command);
    }
}
