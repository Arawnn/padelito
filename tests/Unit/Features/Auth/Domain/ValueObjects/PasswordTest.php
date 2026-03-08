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
    public function testItRejectsAPasswordThatIsTooShort(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must be at least 12 characters long');
        Password::fromPlainText('Short1!');
    }

    public function testItRejectsAPasswordThatDoesNotContainAnUppercaseLetter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');
        Password::fromPlainText('password123!');
    }

    public function testItRejectsAPasswordThatDoesNotContainALowercaseLetter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');
        Password::fromPlainText('PASSWORD123!');
    }

    public function testItRejectsAPasswordThatDoesNotContainANumber(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one number');
        Password::fromPlainText('PasswordAbc!');
    }

    public function testItRejectsAPasswordThatDoesNotContainASpecialCharacter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one special character');
        Password::fromPlainText('Password1234');
    }

    public function testItAcceptsAValidPassword(): void
    {
        $password = Password::fromPlainText('Password123!');
        $this->assertEquals('Password123!', $password->value());
    }

    public function testForVerificationBypassesValidation(): void
    {
        $password = Password::forVerification('weak');
        $this->assertEquals('weak', $password->value());
    }

    public function testItCollectsMultipleViolations(): void
    {
        try {
            Password::fromPlainText('');
        } catch (InvalidPasswordException $e) {
            $this->assertCount(5, $e->violations());

            return;
        }
        $this->fail('Expected InvalidPasswordException to be thrown');
    }
}
