<?php

declare(strict_types=1);

namespace App\Shared\Application\Transaction;

interface TransactionManagerInterface
{
    /**
     * @template TReturn
     *
     * @param callable(): TReturn $fn
     *
     * @return TReturn
     */
    public function run(callable $fn): mixed;

    /**
     * @param callable(void): void $fn
     */
    public function afterCommit(callable $fn): void;
}
