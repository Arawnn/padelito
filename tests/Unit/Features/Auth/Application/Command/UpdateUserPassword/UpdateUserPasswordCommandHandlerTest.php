<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\UpdateUserPassword;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommandHandler;
use App\Features\Auth\Domain\Events\UserPasswordUpdated;
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
final class UpdateUserPasswordCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $repository;

    private FakePasswordHasher $passwordHasher;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryUserRepository;
        $this->passwordHasher = new FakePasswordHasher;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_updates_a_password(): void
    {
        $newPlainPassword = 'Password123!';

        $user = UserMother::create()
            ->withHashedPassword('hashed_old-password')
            ->build();
        $this->repository->save($user);

        $command = new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: $newPlainPassword,
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isOk());

        $this->assertTrue($this->eventDispatcher->dispatched(UserPasswordUpdated::class));

        $expectedHash = $this->passwordHasher->hash(Password::fromPlainText($newPlainPassword))->value();
        $this->assertEquals($expectedHash, $user->password()->value());

        $persisted = $this->repository->findById($user->id());
        $this->assertEquals($expectedHash, $persisted->password()->value());
    }

    public function test_it_returns_an_exception_if_the_user_is_not_found(): void
    {
        $command = new UpdateUserPasswordCommand(
            userId: 'invalid-user-id',
            password: 'Password123!',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserNotFoundException::class, $result->error());
        $this->assertStringContainsString('USER_NOT_FOUND', $result->error()->getDomainCode());
    }

    public function test_it_returns_an_exception_if_the_password_is_invalid(): void
    {
        $user = UserMother::create()->build();
        $this->repository->save($user);

        $command = new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: 'invalid-password',
        );
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidPasswordException::class, $result->error());
        $this->assertStringContainsString('INVALID_PASSWORD', $result->error()->getDomainCode());
    }

    private function makeHandler(): UpdateUserPasswordCommandHandler
    {
        return new UpdateUserPasswordCommandHandler(
            $this->repository,
            $this->passwordHasher,
            $this->eventDispatcher
        );
    }
}
