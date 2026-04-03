<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Result;

final class FakeCommandBus implements CommandBusInterface
{
    private Result $response;

    public function __construct()
    {
        $this->response = Result::void();
    }

    public function willReturn(Result $result): void
    {
        $this->response = $result;
    }

    public function dispatch(object $command): Result
    {
        return $this->response;
    }
}
