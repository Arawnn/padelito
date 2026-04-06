<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidUsernameException;
use App\Features\Player\Domain\ValueObjects\Username;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UsernameTest extends TestCase
{
    public function test_it_accepts_a_valid_username(): void
    {
        $username = Username::fromString('jean_dupont');
        $this->assertEquals('jean_dupont', $username->value());
    }

    public function test_it_accepts_digits_and_underscores(): void
    {
        $this->assertEquals('player_42', Username::fromString('player_42')->value());
    }

    public function test_it_accepts_exactly_30_characters(): void
    {
        $value = str_repeat('a', 30);
        $this->assertEquals($value, Username::fromString($value)->value());
    }

    public function test_it_accepts_minimum_3_characters(): void
    {
        $this->assertEquals('abc', Username::fromString('abc')->value());
    }

    public function test_it_rejects_empty_username(): void
    {
        $this->expectException(InvalidUsernameException::class);
        Username::fromString('');
    }

    public function test_it_rejects_username_shorter_than_3_characters(): void
    {
        $this->expectException(InvalidUsernameException::class);
        $this->expectExceptionMessage('Username must be at least 3 characters long');
        Username::fromString('ab');
    }

    public function test_it_rejects_username_longer_than_30_characters(): void
    {
        $this->expectException(InvalidUsernameException::class);
        $this->expectExceptionMessage('Username must be at most 30 characters long');
        Username::fromString(str_repeat('a', 31));
    }

    public function test_it_rejects_uppercase_letters(): void
    {
        $this->expectException(InvalidUsernameException::class);
        $this->expectExceptionMessage('may only contain lowercase letters');
        Username::fromString('Jean_Dupont');
    }

    public function test_it_rejects_spaces(): void
    {
        $this->expectException(InvalidUsernameException::class);
        Username::fromString('jean dupont');
    }

    public function test_it_rejects_special_characters(): void
    {
        $this->expectException(InvalidUsernameException::class);
        Username::fromString('jean-dupont');
    }

    public function test_it_rejects_accented_characters(): void
    {
        $this->expectException(InvalidUsernameException::class);
        Username::fromString('élodie');
    }
}
