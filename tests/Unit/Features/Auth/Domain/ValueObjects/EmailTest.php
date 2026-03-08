<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EmailTest extends TestCase
{
    public function test_it_validates_an_email(): void
    {
        $email = Email::fromString('john.doe@example.com');
        $this->assertEquals('john.doe@example.com', $email->value());
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('invalid-email');
    }

    public function test_it_rejects_an_empty_email(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('');
    }

    public function test_it_rejects_a_whitespace_only_string_as_email(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString(' ');
    }

    public function test_it_rejects_an_email_with_a_space_in_the_domain(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doe@ example.com');
    }

    public function test_it_rejects_an_email_without_domain(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doe@');
    }

    public function test_it_rejects_an_email_without_arobase(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doeexample.com');
    }

    public function test_it_rejects_an_email_without_a_local_part(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('@example.com');
    }
}
