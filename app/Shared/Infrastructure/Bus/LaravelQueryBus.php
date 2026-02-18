<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use Illuminate\Container\Container;
use App\Shared\Application\Bus\QueryBusInterface;

final readonly class LaravelQueryBus implements QueryBusInterface {
    public function __construct(
        private Container $container
    ) {}

    public function ask(object $query): mixed
    {
        $handler = $this->container->make(get_class($query) . 'Handler');
        return $handler($query);
    }
}