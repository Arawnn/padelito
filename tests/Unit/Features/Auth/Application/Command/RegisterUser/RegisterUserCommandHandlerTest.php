<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\RegisterUser;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\InvalidNameException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistsException;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryUserRepository;
        $this->tx = new ImmediateTransactionManager;
        $this->uuidGenerator = new FakeUuidGenerator;
        $this->passwordHasher = new FakePasswordHasher;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_registers_a_user(): void
    {
        $handler = $this->makeHandler();

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);

        $user = $this->repository->findById($result->value()->id());

        $this->assertNotNull($user);
        $this->assertTrue($result->isOk());

        $this->assertEquals('john.doe@example.com', $user->email()->value());
        $this->assertEquals('John Doe', $user->name()->value());
        $this->assertEquals('00000000-0000-0000-0000-000000000000', $user->id()->value());
        $this->assertEquals('hashed_Password123!', $user->password()->value());

        $this->assertInstanceOf(User::class, $result->value());
        $this->assertTrue($this->eventDispatcher->dispatched(UserCreated::class));
    }

    public function test_it_returns_an_exception_if_the_user_email_already_exists(): void
    {
        $this->repository->save(
            UserMother::create()->withEmail('john.doe@example.com')->build()
        );

        $handler = $this->makeHandler();

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserAlreadyExistsException::class, $result->error());
        $this->assertStringContainsString('USER_ALREADY_EXISTS', $result->error()->getDomainCode());
    }

    public function test_it_returns_an_exception_if_the_user_id_already_exists(): void
    {
        $this->repository->save(
            UserMother::create()->withId('00000000-0000-0000-0000-000000000000')->build()
        );

        $handler = $this->makeHandler();

        $command = new RegisterUserCommand(
            name: 'John Doe',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserAlreadyExistsException::class, $result->error());
        $this->assertStringContainsString('USER_ALREADY_EXISTS', $result->error()->getDomainCode());
    }

    public function test_it_returns_an_exception_if_the_password_is_invalid(): void
    {
        $handler = $this->makeHandler();

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

    public function test_it_returns_an_exception_if_the_email_is_invalid(): void
    {
        $command = new RegisterUserCommand(
            name: 'John',
            email: 'invalid-email',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidEmailException::class, $result->error());
        $this->assertStringContainsString('INVALID_EMAIL', $result->error()->getDomainCode());
    }

    public function test_it_returns_an_exception_if_the_name_is_invalid(): void
    {
        $command = new RegisterUserCommand(
            name: 'Jo',
            email: 'john.doe@example.com',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidNameException::class, $result->error());
        $this->assertStringContainsString('INVALID_NAME', $result->error()->getDomainCode());
    }

    private function makeHandler(): RegisterUserCommandHandler
    {
        return new RegisterUserCommandHandler(
            $this->repository,
            $this->tx,
            $this->passwordHasher,
            $this->uuidGenerator,
            $this->eventDispatcher
        );
    }
}
