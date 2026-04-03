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

    public function test_it_rejects_a_display_name_that_is_too_short(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('DisplayName must be at least 3 characters long');
        DisplayName::fromString('Jo');
    }

    public function test_it_rejects_a_display_name_that_is_too_long(): void
    {
        $this->expectException(InvalidDisplayNameException::class);
        $this->expectExceptionMessage('DisplayName must be less than 256 characters long');
        DisplayName::fromString('John Doe'.str_repeat('a', 256));
    }

    public function test_it_accepts_a_display_name_of_exactly255_characters(): void
    {
        $displayName = DisplayName::fromString(str_repeat('a', 255));
        $this->assertEquals(str_repeat('a', 255), $displayName->value());
    }
}
