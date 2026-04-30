<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Infrastructure\Bus\LaravelCommandBus;
use App\Shared\Infrastructure\Transaction\LaravalTransactionManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Tests\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LaravelCommandBusTransactionTest extends IntegrationTestCase
{
    public function test_synchronous_listener_failure_rolls_back_handler_writes(): void
    {
        $events = $this->app->make(Dispatcher::class);
        $events->listen(
            TransactionalCommandBusTestEvent::class,
            ThrowingTransactionalCommandBusTestListener::class,
        );

        $handlers = new HandlerMap;
        $handlers->register(InsertUserThenDispatchEventCommand::class, InsertUserThenDispatchEventCommandHandler::class);

        $bus = new LaravelCommandBus($handlers, new LaravalTransactionManager);

        try {
            $bus->dispatch(new InsertUserThenDispatchEventCommand);
            $this->fail('Expected synchronous listener failure.');
        } catch (SynchronousListenerFailedException $exception) {
            $this->assertSame('Synchronous listener failed.', $exception->getMessage());
        }

        $this->assertSame(
            0,
            DB::table('users')->where('email', 'rollback@example.com')->count()
        );
    }
}

final readonly class InsertUserThenDispatchEventCommand {}

final readonly class InsertUserThenDispatchEventCommandHandler
{
    public function __construct(private Dispatcher $events) {}

    public function __invoke(InsertUserThenDispatchEventCommand $command): void
    {
        DB::table('users')->insert([
            'id' => '00000000-0000-0000-0000-000000000123',
            'name' => 'Rollback User',
            'email' => 'rollback@example.com',
            'password' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->events->dispatch(new TransactionalCommandBusTestEvent);
    }
}

final readonly class TransactionalCommandBusTestEvent {}

final readonly class ThrowingTransactionalCommandBusTestListener
{
    public function handle(): void
    {
        throw new SynchronousListenerFailedException('Synchronous listener failed.');
    }
}

final class SynchronousListenerFailedException extends \RuntimeException {}
