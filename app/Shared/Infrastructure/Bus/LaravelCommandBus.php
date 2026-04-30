<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Application\Transaction\TransactionManagerInterface;

final readonly class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private HandlerMap $handlers,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function dispatch(object $command): mixed
    {
        return $this->transactionManager->run(function () use ($command): mixed {
            $handlerClass = $this->handlers->handlerFor($command);
            $handler = app($handlerClass);

            return $handler($command);
        });
    }
}
