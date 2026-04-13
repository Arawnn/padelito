<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidBioException;
use App\Features\Player\Domain\ValueObjects\Bio;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class BioTest extends TestCase
{
    public function test_it_accepts_a_valid_bio(): void
    {
        $bio = Bio::fromString('Je joue au padel depuis 2 ans.');
        $this->assertEquals('Je joue au padel depuis 2 ans.', $bio->value());
    }

    public function test_it_accepts_an_empty_bio(): void
    {
        $bio = Bio::fromString('');
        $this->assertEquals('', $bio->value());
    }

    public function test_it_accepts_exactly_120_characters(): void
    {
        $value = str_repeat('a', 120);
        $this->assertEquals($value, Bio::fromString($value)->value());
    }

    public function test_it_rejects_a_bio_longer_than_120_characters(): void
    {
        $this->expectException(InvalidBioException::class);
        $this->expectExceptionMessage('Bio must be at most 120 characters long');
        Bio::fromString(str_repeat('a', 121));
    }
}
