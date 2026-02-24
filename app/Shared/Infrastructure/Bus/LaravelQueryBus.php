<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Application\Bus\QueryBusInterface;

final readonly class LaravelQueryBus implements QueryBusInterface {
    public function __construct(
        private HandlerMap $handlers
    ) {}

    public function ask(object $query): mixed
    {
        $handlerClass = $this->handlers->handlerFor($query);
        $handler = app($handlerClass);
        return $handler($query);
    }
}