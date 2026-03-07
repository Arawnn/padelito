<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\RegisterUser;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use Tests\Shared\Mother\Fake\FakePasswordHasher;
use Tests\Shared\Mother\Fake\FakeUuidGenerator;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RegisterUserCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $repository;
    private ImmediateTransactionManager $tx;
    private FakeUuidGenerator $uuidGenerator;
    private FakePasswordHasher $passwordHasher;
    private SpyEventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryUserRepository();
        $this->tx = new ImmediateTransactionManager();
        $this->uuidGenerator = new FakeUuidGenerator();
        $this->passwordHasher = new FakePasswordHasher();
        $this->eventDispatcher = new SpyEventDispatcher();
    }
    public function testItRegistersAUser(): void
    {
        $handler = new RegisterUserCommandHandler(
            $this->repository,
            $this->tx,
            $this->passwordHasher,
            $this->uuidGenerator,
            $this->eventDispatcher
        );

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);

        $user = $this->repository->findById($result->value()->id());

        $this->assertNotNull($user);
        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(User::class, $result->value());
        $this->assertTrue($this->eventDispatcher->dispatched(UserCreated::class));
    }

    public function testItReturnsAnExceptionIfTheUserAlreadyExists(): void
    {
        $this->repository->create(UserMother::register());
        $handler = new RegisterUserCommandHandler(
            $this->repository,
            $this->tx,
            $this->passwordHasher,
            $this->uuidGenerator,
            $this->eventDispatcher
        );

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);
        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserAlreadyExistException::class, $result->error());
        $this->assertStringContainsString('USER_ALREADY_EXISTS', $result->error()->getDomainCode());
    }

    public function testItReturnsAnExceptionIfThePasswordIsInvalid(): void
    {
        $handler = new RegisterUserCommandHandler(
            $this->repository,
            $this->tx,
            $this->passwordHasher,
            $this->uuidGenerator,
            $this->eventDispatcher
        );

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'invalid',
        );

        $result = $handler($command);
        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidPasswordException::class, $result->error());
        $this->assertStringContainsString('INVALID_PASSWORD', $result->error()->getDomainCode());
    }
}
