<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\ValueObjects\Password;
use PHPUnit\Framework\TestCase;

final class PasswordTest extends TestCase
{
    public function test_it_rejects_a_password_that_is_too_short(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must be at least 12 characters long');
        Password::fromPlainText('Short1!');
    }

    public function test_it_rejects_a_password_that_does_not_contain_an_uppercase_letter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');
        Password::fromPlainText('password123!');
    }

    public function test_it_rejects_a_password_that_does_not_contain_a_lowercase_letter(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');
        Password::fromPlainText('PASSWORD123!');
    }

    public function test_it_rejects_a_password_that_does_not_contain_a_number(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one number');
        Password::fromPlainText('PasswordAbc!');
    }

    public function test_it_rejects_a_password_that_does_not_contain_a_special_character(): void
    {
        $this->expectException(InvalidPasswordException::class);
        $this->expectExceptionMessage('Password must contain at least one special character');
        Password::fromPlainText('Password1234');
    }

    public function test_it_accepts_a_valid_password(): void
    {
        $password = Password::fromPlainText('Password123!');
        $this->assertEquals('Password123!', $password->value());
    }

    public function test_for_verification_bypasses_validation(): void
    {
        $password = Password::forVerification('weak');
        $this->assertEquals('weak', $password->value());
    }

    public function test_it_collects_multiple_violations(): void
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
