<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\ConfirmPasswordReset;

use App\Features\Auth\Application\Commands\ConfirmPasswordReset\ConfirmPasswordResetCommand;
use App\Features\Auth\Application\Commands\ConfirmPasswordReset\ConfirmPasswordResetCommandHandler;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Result;
use Tests\Shared\Mother\Fake\FakeCommandBus;
use Tests\Shared\Mother\Fake\InMemoryPasswordResetTokenRepository;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ConfirmPasswordResetCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;

    private InMemoryPasswordResetTokenRepository $tokenRepository;

    private FakeCommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new InMemoryUserRepository;
        $this->tokenRepository = new InMemoryPasswordResetTokenRepository;
        $this->commandBus = new FakeCommandBus;
    }

    public function test_it_confirms_a_password_reset(): void
    {
        $email = 'john.doe@example.com';
        $user = UserMother::create()->withEmail($email)->build();
        $this->userRepository->create($user);
        $token = $this->tokenRepository->create(Email::fromString($email));

        $command = new ConfirmPasswordResetCommand(
            email: $email,
            token: $token,
            password: 'NewPassword123!',
        );
        $handler = new ConfirmPasswordResetCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->commandBus,
        );

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value());

        // Le token doit être supprimé après confirmation
        $this->assertFalse($this->tokenRepository->isValid(Email::fromString($email), $token));
    }

    public function test_it_returns_an_exception_if_the_user_is_not_found(): void
    {
        $command = new ConfirmPasswordResetCommand(
            email: 'unknown@example.com',
            token: 'any-token',
            password: 'NewPassword123!',
        );
        $handler = new ConfirmPasswordResetCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->commandBus,
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UserNotFoundException::class, $result->error());
        $this->assertStringContainsString('USER_NOT_FOUND', $result->error()->getDomainCode());
    }

    public function test_it_returns_an_exception_if_the_token_is_invalid(): void
    {
        $email = 'john.doe@example.com';
        $user = UserMother::create()->withEmail($email)->build();
        $this->userRepository->create($user);
        // Aucun token créé dans le repo → isValid() retourne false

        $command = new ConfirmPasswordResetCommand(
            email: $email,
            token: 'invalid-or-expired-token',
            password: 'NewPassword123!',
        );
        $handler = new ConfirmPasswordResetCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->commandBus,
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidResetTokenException::class, $result->error());
        $this->assertStringContainsString('INVALID_RESET_TOKEN', $result->error()->getDomainCode());
    }

    public function test_it_propagates_the_error_if_the_password_update_fails(): void
    {
        $email = 'john.doe@example.com';
        $user = UserMother::create()->withEmail($email)->build();
        $this->userRepository->create($user);
        $token = $this->tokenRepository->create(Email::fromString($email));

        $this->commandBus->willReturn(
            Result::fail(InvalidPasswordException::fromViolations(['Password too weak']))
        );

        $command = new ConfirmPasswordResetCommand(
            email: $email,
            token: $token,
            password: 'weak',
        );
        $handler = new ConfirmPasswordResetCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->commandBus,
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidPasswordException::class, $result->error());

        // Le token ne doit PAS être supprimé si la mise à jour échoue
        $this->assertTrue($this->tokenRepository->isValid(Email::fromString($email), $token));
    }

    public function test_it_returns_an_exception_if_the_email_is_invalid(): void
    {
        $command = new ConfirmPasswordResetCommand(
            email: 'invalid-email',
            token: 'any-token',
            password: 'NewPassword123!',
        );
        $handler = new ConfirmPasswordResetCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->commandBus,
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidEmailException::class, $result->error());
        $this->assertStringContainsString('INVALID_EMAIL', $result->error()->getDomainCode());
    }
}
