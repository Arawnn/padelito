<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Application\Command\SendPasswordResetEmail;

use App\Features\Auth\Application\Commands\SendPasswordResetEmail\SendPasswordResetEmailCommand;
use App\Features\Auth\Application\Commands\SendPasswordResetEmail\SendPasswordResetEmailCommandHandler;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use Tests\Shared\Mother\Fake\InMemoryPasswordResetTokenRepository;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyMailer;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SendPasswordResetEmailCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;

    private InMemoryPasswordResetTokenRepository $tokenRepository;

    private SpyMailer $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new InMemoryUserRepository;
        $this->tokenRepository = new InMemoryPasswordResetTokenRepository;
        $this->mailer = new SpyMailer;
    }

    public function test_it_sends_a_password_reset_email(): void
    {
        $user = UserMother::create()->withEmail('john.doe@example.com')->build();
        $this->userRepository->save($user);

        $command = new SendPasswordResetEmailCommand(email: 'john.doe@example.com');
        $handler = new SendPasswordResetEmailCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->mailer,
        );

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value());
        $this->assertTrue($this->mailer->wasSentTo('john.doe@example.com'));
        $this->assertEquals(1, $this->mailer->count());
    }

    public function test_it_does_not_send_an_email_if_the_user_does_not_exist(): void
    {
        $command = new SendPasswordResetEmailCommand(email: 'unknown@example.com');
        $handler = new SendPasswordResetEmailCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->mailer,
        );

        $result = $handler($command);

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value());
        $this->assertEquals(0, $this->mailer->count());
    }

    public function test_it_returns_an_exception_if_the_email_is_invalid(): void
    {
        $command = new SendPasswordResetEmailCommand(email: 'invalid-email');
        $handler = new SendPasswordResetEmailCommandHandler(
            $this->userRepository,
            $this->tokenRepository,
            $this->mailer,
        );

        $result = $handler($command);

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(InvalidEmailException::class, $result->error());
        $this->assertStringContainsString('INVALID_EMAIL', $result->error()->getDomainCode());
        $this->assertEquals(0, $this->mailer->count());
    }
}
