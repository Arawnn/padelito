<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\CommandBusInterface;
use Illuminate\Container\Container;

final readonly class LaravelCommandBus implements CommandBusInterface {
    public function __construct(
        private Container $container
    ) {}

    public function dispatch(object $command): void
    {
        $handler = $this->container->make(get_class($command) . 'Handler');
        $handler($command);
    }
}