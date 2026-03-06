<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\RegisterUser;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserCreated;
use Tests\Shared\Mother\Fake\FakePasswordHasher;
use Tests\Shared\Mother\Fake\FakeUuidGenerator;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RegisterUserCommandHandlerTest extends TestCase
{
    public function testItRegistersAUser(): void
    {
        $repository = new InMemoryUserRepository();
        $tx = new ImmediateTransactionManager();
        $uuidGenerator = new FakeUuidGenerator();
        $passwordHasher = new FakePasswordHasher();
        $eventDispatcher = new SpyEventDispatcher();

        $handler = new RegisterUserCommandHandler(
            $repository,
            $tx,
            $passwordHasher,
            $uuidGenerator,
            $eventDispatcher
        );

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);

        $user = $repository->findById($result->value()->id());

        $this->assertNotNull($user);
        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(User::class, $result->value());
        $this->assertTrue($eventDispatcher->dispatched(UserCreated::class));
    }
}
