<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Application\Bus\CommandBusInterface;

final readonly class LaravelCommandBus implements CommandBusInterface {
    public function __construct(
        private HandlerMap $handlers
    ) {}

    public function dispatch(object $command): void
    {
        $handlerClass = $this->handlers->handlerFor($command);
        $handler = app($handlerClass);
        $handler($command);
    }
}