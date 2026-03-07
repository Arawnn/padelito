<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\LogoutUser;

use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommand;
use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommandHandler;
use App\Features\Auth\Domain\Events\UserLoggedOut;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

final class LogoutUserCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $repository;
    private SpyEventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryUserRepository();
        $this->eventDispatcher = new SpyEventDispatcher();
    }

    public function testItLogsOutAUser(): void
    {
        $user = UserMother::create()->build();
        $this->repository->create($user);

        $command = new LogoutUserCommand(userId: $user->id()->value());
        $handler = new LogoutUserCommandHandler(
            $this->repository,
            $this->eventDispatcher
        );

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value());
        $this->assertTrue($this->eventDispatcher->dispatched(UserLoggedOut::class));
    }

    public function testItReturnsAnExceptionIfTheUserIsNotFound(): void
    {
        $command = new LogoutUserCommand(userId: 'invalid-user-id');
        $handler = new LogoutUserCommandHandler(
            $this->repository,
            $this->eventDispatcher
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserNotFoundException::class, $result->error());
        $this->assertStringContainsString('USER_NOT_FOUND', $result->error()->getDomainCode());
    }
}
