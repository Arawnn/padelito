<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Infrastructure\Exceptions\InfrastructureException;

final class HandlerMap
{
    /** @var array<class-string, class-string> */
    private array $map = [];

    public function register(string $messageClass, string $handlerClass): void
    {
        $this->map[$messageClass] = $handlerClass;
    }

    public function handlerFor(object $message): string
    {
        $cls = $message::class;
        if (! isset($this->map[$cls])) {
            throw InfrastructureException::handlerNotFound($cls);
        }

        return $this->map[$cls];
    }
}
