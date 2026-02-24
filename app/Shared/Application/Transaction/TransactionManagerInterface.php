<?php

declare(strict_types=1);

namespace App\Shared\Application\Transaction;

interface TransactionManagerInterface
{
    public function run(callable $fn): mixed;
    public function afterCommit(callable $fn): void;
}