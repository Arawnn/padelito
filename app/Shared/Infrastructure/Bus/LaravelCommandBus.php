<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\HandlerMap;

final readonly class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private HandlerMap $handlers
    ) {}

    public function dispatch(object $command): mixed
    {
        $handlerClass = $this->handlers->handlerFor($command);
        $handler = app($handlerClass);

        return $handler($command);
    }
}
