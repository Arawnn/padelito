<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\ValueObjects\Password;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PasswordTest extends TestCase
{
    public function testItRejectPasswordThatIsTooShort(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must be at least 12 characters long');
        Password::fromPlainText('short');
    }

    public function testItRejectPasswordThatDoesNotContainAnUppercaseLetter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');
        Password::fromPlainText('password');
    }

    public function testItRejectPasswordThatDoesNotContainALowercaseLetter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');
        Password::fromPlainText('PASSWORD');
    }

    public function testItRejectPasswordThatDoesNotContainANumber(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one number');
        Password::fromPlainText('password');
    }

    public function testItRejectPasswordThatDoesNotContainASpecialCharacter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one special character');
        Password::fromPlainText('password');
    }

    public function testItAcceptsAValidPassword(): void
    {
        $password = Password::fromPlainText('Password123!');
        $this->assertEquals('Password123!', $password->value());
    }
}
