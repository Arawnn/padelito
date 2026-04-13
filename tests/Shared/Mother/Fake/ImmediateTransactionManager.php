<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Transaction\TransactionManagerInterface;

final class ImmediateTransactionManager implements TransactionManagerInterface
{
    private ?\Closure $afterCommitCallback = null;

    private bool $inTransaction = false;

    public function run(callable $fn): mixed
    {
        $this->inTransaction = true;
        $result = $fn();
        $this->inTransaction = false;

        if ($this->afterCommitCallback !== null) {
            ($this->afterCommitCallback)();
            $this->afterCommitCallback = null;
        }

        return $result;
    }

    public function afterCommit(callable $fn): void
    {
        if ($this->inTransaction) {
            // Defer until run() completes (like a real DB transaction commit hook).
            $this->afterCommitCallback = \Closure::fromCallable($fn);
        } else {
            // No active transaction: fire immediately (mirrors Laravel DB::afterCommit).
            $fn();
        }
    }
}
