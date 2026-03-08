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

/**
 * @internal
 *
 * @coversNothing
 */
final class LogoutUserCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryUserRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_logs_out_a_user(): void
    {
        $user = UserMother::create()->build();
        $this->repository->create($user);

        $command = new LogoutUserCommand(userId: $user->id()->value());
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value());
        $this->assertTrue($this->eventDispatcher->dispatched(UserLoggedOut::class));
    }

    public function test_it_returns_an_exception_if_the_user_is_not_found(): void
    {
        $command = new LogoutUserCommand(userId: 'invalid-user-id');
        $handler = $this->makeHandler();

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserNotFoundException::class, $result->error());
        $this->assertStringContainsString('USER_NOT_FOUND', $result->error()->getDomainCode());
    }

    private function makeHandler(): LogoutUserCommandHandler
    {
        return new LogoutUserCommandHandler(
            $this->repository,
            $this->eventDispatcher
        );
    }
}
