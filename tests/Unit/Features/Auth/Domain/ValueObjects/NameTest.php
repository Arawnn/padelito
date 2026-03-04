<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidNameException;
use App\Features\Auth\Domain\ValueObjects\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
    public function test_it_validates_a_name(): void
    {
        $name = Name::fromString('John Doe');
        $this->assertEquals('John Doe', $name->value());
    }

    public function test_it_rejects_a_name_that_is_too_short(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name must be at least 3 characters long');
        Name::fromString('Jo');
    }

    public function test_it_rejects_a_name_that_is_too_long(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name must be less than 256 characters long');
        Name::fromString('John Doe'.str_repeat('a', 256));
    }

    public function test_it_accepts_a_name_of_exactly_255_characters (): void
    {
        $name = Name::fromString(str_repeat('a', 255));
        $this->assertEquals(str_repeat('a', 255), $name->value());
    }

    public function test_it_rejects_empty_name(): void
    {
        $this->expectException(InvalidNameException::class);
        $this->expectExceptionMessage('Name cannot be empty');
        Name::fromString('');
    }

    public function test_it_rejects_a_name_of_exactly_256_characters(): void
    {
        $this->expectExceptionMessage('Name must be less than 256 characters long');
        $this->expectException(InvalidNameException::class);
        Name::fromString(str_repeat('a', 256));
    }

    public function test_it_rejects_a_whitespace_only_name(): void
    {
        $this->expectException(InvalidNameException::class);
        Name::fromString('   ');
    }
}