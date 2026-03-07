<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testItValidatesAnEmail(): void
    {
        $email = Email::fromString('john.doe@example.com');
        $this->assertEquals('john.doe@example.com', $email->value());
    }

    public function testItRejectsAnInvalidEmail(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('invalid-email');
    }

    public function testItRejectsAnEmptyEmail(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('');
    }

    public function testItRejectsAWhitespaceOnlyStringAsEmail(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString(' ');
    }

    public function testItRejectsAnEmailWithASpaceInTheDomain(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doe@ example.com');
    }

    public function testItRejectsAnEmailWithoutDomain(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doe@');
    }

    public function testItRejectsAnEmailWithoutArobase(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('john.doeexample.com');
    }

    public function testItRejectsAnEmailWithoutALocalPart(): void
    {
        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('@example.com');
    }
}
