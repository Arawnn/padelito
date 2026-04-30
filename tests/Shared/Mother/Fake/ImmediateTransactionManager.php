<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Transaction\TransactionManagerInterface;

final class ImmediateTransactionManager implements TransactionManagerInterface
{
    private bool $inTransaction = false;

    private int $runs = 0;

    public function run(callable $fn): mixed
    {
        $wasInTransaction = $this->inTransaction;
        $this->inTransaction = true;
        $this->runs++;

        try {
            return $fn();
        } finally {
            $this->inTransaction = $wasInTransaction;
        }
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function runs(): int
    {
        return $this->runs;
    }
}
