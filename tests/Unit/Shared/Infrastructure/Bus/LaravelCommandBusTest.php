<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Infrastructure\Bus\LaravelCommandBus;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LaravelCommandBusTest extends TestCase
{
    public function test_dispatch_wraps_handler_in_transaction_and_returns_result(): void
    {
        $transactionManager = new ImmediateTransactionManager;
        $handlers = new HandlerMap;
        $probe = new \stdClass;
        $command = new class('payload')
        {
            public function __construct(public string $payload) {}
        };
        $handler = new class($transactionManager, $probe)
        {
            public function __construct(
                private ImmediateTransactionManager $transactionManager,
                private \stdClass $probe,
            ) {}

            public function __invoke(object $command): string
            {
                $this->probe->wasInTransaction = $this->transactionManager->inTransaction();

                return 'handled: '.$command->payload;
            }
        };

        $handlers->register($command::class, $handler::class);

        $this->app->bind(
            $handler::class,
            fn () => $handler
        );

        $bus = new LaravelCommandBus($handlers, $transactionManager);

        $result = $bus->dispatch($command);

        $this->assertSame('handled: payload', $result);
        $this->assertTrue($probe->wasInTransaction);
        $this->assertSame(1, $transactionManager->runs());
    }
}
