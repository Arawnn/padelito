<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Query\GetCurrentUser;

use App\Features\Auth\Application\Queries\GetCurrentUser\GetCurrentUserQuery;
use App\Features\Auth\Application\Queries\GetCurrentUser\GetCurrentUserQueryHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GetCurrentUserQueryHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new InMemoryUserRepository;
    }

    public function test_it_returns_the_current_user_by_id(): void
    {
        $user = UserMother::create()
            ->withId('00000000-0000-0000-0000-000000000123')
            ->withEmail('john.doe@example.com')
            ->build();
        $this->userRepository->save($user);

        $handler = new GetCurrentUserQueryHandler($this->userRepository);
        $result = $handler(new GetCurrentUserQuery(userId: '00000000-0000-0000-0000-000000000123'));

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id()->value(), $result->id()->value());
        $this->assertEquals($user->name()->value(), $result->name()->value());
        $this->assertEquals($user->email()->value(), $result->email()->value());
    }

    public function test_it_fails_when_the_current_user_is_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $handler = new GetCurrentUserQueryHandler($this->userRepository);
        $handler(new GetCurrentUserQuery(userId: '00000000-0000-0000-0000-000000000404'));
    }
}
