<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Application\Result;

interface QueryBusInterface
{
    public function ask(object $query): Result;
}
