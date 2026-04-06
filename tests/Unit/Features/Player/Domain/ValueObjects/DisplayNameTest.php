<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidDisplayNameException;
use App\Features\Player\Domain\ValueObjects\DisplayName;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DisplayNameTest extends TestCase
{
    public function test_it_validates_a_display_name(): void
    {
        $displayName = DisplayName::fromString('John Doe');
        $this->assertEquals('John Doe', $displayName->value());
    }

    public function test_it_accepts_accented_characters(): void
    {
        $displayName = DisplayName::fromString('Élodie Müller');
        $this->assertEquals('Élodie Müller', $displayName->value());
    }

    public function test_it_accepts_a_display_name_of_exactly_30_characters(): void
    {
        $name = str_repeat('A', 30);
        $this->assertEquals($name, DisplayName::fromString($name)->value());
    }

    public function test_it_rejects_an_empty_display_name(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('Display name cannot be empty');
        DisplayName::fromString('');
    }

    public function test_it_rejects_a_display_name_that_is_too_long(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('Display name must be at most 30 characters long');
        DisplayName::fromString(str_repeat('A', 31));
    }

    public function test_it_rejects_digits_in_display_name(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('Display name may only contain letters and spaces');
        DisplayName::fromString('John123');
    }

    public function test_it_rejects_special_characters_in_display_name(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('Display name may only contain letters and spaces');
        DisplayName::fromString('John_Doe');
    }
}
