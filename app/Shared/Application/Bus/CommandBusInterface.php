<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Domain\ValueObjects\Result;

interface CommandBusInterface
{
    public function dispatch(object $command): Result;
}
