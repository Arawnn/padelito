<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\LoginUser;

use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommand;
use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommandHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserLoggedIn;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\ValueObjects\Password;
use Tests\Shared\Mother\Fake\FakePasswordHasher;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LoginUserCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $repository;
    private FakePasswordHasher $passwordHasher;
    private SpyEventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryUserRepository();
        $this->passwordHasher = new FakePasswordHasher();
        $this->eventDispatcher = new SpyEventDispatcher();
    }

    public function testItLogsInAUser(): void
    {
        $plainPassword = 'fake-pour-test';
        $hashedPassword = $this->passwordHasher->hash(Password::forVerification($plainPassword));

        $user = UserMother::create()
            ->withHashedPassword($hashedPassword->value())
            ->build()
        ;
        $this->repository->create($user);

        $command = new LoginUserCommand(
            email: 'john.doe@example.com',
            password: $plainPassword,
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertInstanceOf(User::class, $result->value());
        $this->assertTrue($this->eventDispatcher->dispatched(UserLoggedIn::class));
        $this->assertEquals($user->email()->value(), $result->value()->email()->value());
        $this->assertEquals($user->name()->value(), $result->value()->name()->value());
        $this->assertEquals($user->id()->value(), $result->value()->id()->value());
        $this->assertEquals($hashedPassword->value(), $result->value()->password()->value());
    }

    public function testItReturnsAnExceptionIfTheUserIsNotFound(): void
    {
        $command = new LoginUserCommand(
            email: 'john.doe@example.com',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserNotFoundException::class, $result->error());
        $this->assertStringContainsString('USER_NOT_FOUND', $result->error()->getDomainCode());
    }

    public function testItReturnsAnExceptionIfThePasswordIsInvalid(): void
    {
        $user = UserMother::create()
            ->withHashedPassword('hashed_fake-pour-test')
            ->build()
        ;
        $this->repository->create($user);

        $command = new LoginUserCommand(
            email: 'john.doe@example.com',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidPasswordException::class, $result->error());
        $this->assertStringContainsString('INVALID_PASSWORD', $result->error()->getDomainCode());
    }

    public function testItReturnsAnExceptionIfTheEmailIsInvalid(): void
    {
        $command = new LoginUserCommand(
            email: 'invalid-email',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidEmailException::class, $result->error());
        $this->assertStringContainsString('INVALID_EMAIL', $result->error()->getDomainCode());
    }

    private function makeHandler(): LoginUserCommandHandler
    {
        return new LoginUserCommandHandler(
            $this->repository,
            $this->passwordHasher,
            $this->eventDispatcher
        );
    }
}
