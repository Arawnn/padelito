<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Query\GetUserByEmail;

use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQueryHandler;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GetUserByEmailQueryHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new InMemoryUserRepository;
    }

    public function test_it_returns_a_user_by_email(): void
    {
        $user = UserMother::create()->withEmail('john.doe@example.com')->build();
        $this->userRepository->save($user);

        $handler = new GetUserByEmailQueryHandler($this->userRepository);
        $result = $handler(new GetUserByEmailQuery(email: 'john.doe@example.com'));

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id()->value(), $result->id()->value());
        $this->assertEquals('john.doe@example.com', $result->email()->value());
        $this->assertEquals($user->name()->value(), $result->name()->value());
    }

    public function test_it_returns_a_failure_if_the_user_is_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $handler = new GetUserByEmailQueryHandler($this->userRepository);
        $handler(new GetUserByEmailQuery(email: 'unknown@example.com'));
    }

    public function test_it_returns_a_failure_if_the_email_is_invalid(): void
    {
        $this->expectException(InvalidEmailException::class);

        $handler = new GetUserByEmailQueryHandler($this->userRepository);
        $handler(new GetUserByEmailQuery(email: 'invalid-email'));
    }
}
