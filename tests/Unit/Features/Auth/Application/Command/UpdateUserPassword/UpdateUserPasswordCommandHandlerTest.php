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

        $handler = $this->makeHandler();
        $handler(new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: $newPlainPassword,
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(UserPasswordUpdated::class));

        $expectedHash = $this->passwordHasher->hash(Password::fromPlainText($newPlainPassword))->value();
        $this->assertEquals($expectedHash, $user->password()->value());

        $persisted = $this->repository->findById($user->id());
        $this->assertEquals($expectedHash, $persisted->password()->value());
    }

    public function test_it_returns_an_exception_if_the_user_is_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $handler = $this->makeHandler();
        $handler(new UpdateUserPasswordCommand(
            userId: 'invalid-user-id',
            password: 'Password123!',
        ));
    }

    public function test_it_returns_an_exception_if_the_password_is_invalid(): void
    {
        $this->expectException(InvalidPasswordException::class);

        $user = UserMother::create()->build();
        $this->repository->save($user);

        $handler = $this->makeHandler();
        $handler(new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: 'invalid-password',
        ));
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
