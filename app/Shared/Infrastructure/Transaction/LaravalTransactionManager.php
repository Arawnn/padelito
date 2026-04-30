<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Transaction;

use App\Shared\Application\Transaction\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

final class LaravalTransactionManager implements TransactionManagerInterface
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $fn
     * @return TReturn
     */
    public function run(callable $fn): mixed
    {
        return DB::transaction($fn);
    }
}
