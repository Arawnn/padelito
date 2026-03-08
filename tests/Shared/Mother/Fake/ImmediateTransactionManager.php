<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Application\Transaction\TransactionManagerInterface;

final class ImmediateTransactionManager implements TransactionManagerInterface
{
    private ?\Closure $afterCommitCallback = null;

    public function run(callable $fn): mixed
    {
        $result = $fn();
        if ($this->afterCommitCallback) {
            ($this->afterCommitCallback)();
        }

        return $result;
    }

    public function afterCommit(callable $fn): void
    {
        $this->afterCommitCallback = \Closure::fromCallable($fn);
    }
}
