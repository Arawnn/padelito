<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidNameException;
use App\Features\Auth\Domain\ValueObjects\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
    public function testItValidatesAName(): void
    {
        $name = Name::fromString('John Doe');
        $this->assertEquals('John Doe', $name->value());
    }

    public function testItRejectsANameThatIsTooShort(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name must be at least 3 characters long');
        Name::fromString('Jo');
    }

    public function testItRejectsANameThatIsTooLong(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name must be less than 256 characters long');
        Name::fromString('John Doe'.str_repeat('a', 256));
    }

    public function testItAcceptsANameOfExactly255Characters(): void
    {
        $name = Name::fromString(str_repeat('a', 255));
        $this->assertEquals(str_repeat('a', 255), $name->value());
    }

    public function testItRejectsEmptyName(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name cannot be empty');
        Name::fromString('');
    }

    public function testItRejectsANameOfExactly256Characters(): void
    {
        $this->expectExceptionMessage('Name must be less than 256 characters long');
        $this->expectException(InvalidNameException::class);
        Name::fromString(str_repeat('a', 256));
    }

    public function testItRejectsAWhitespaceOnlyName(): void
    {
        $this->expectException(InvalidNameException::class);
        Name::fromString('   ');
    }
}
