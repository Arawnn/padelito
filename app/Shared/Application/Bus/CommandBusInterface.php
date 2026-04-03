<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Application\Result;

interface CommandBusInterface
{
    public function dispatch(object $command): Result;
}
