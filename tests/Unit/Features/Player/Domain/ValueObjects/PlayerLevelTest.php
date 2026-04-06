<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\ValueObjects\PlayerLevel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PlayerLevelTest extends TestCase
{
    public function test_it_wraps_each_enum_case(): void
    {
        foreach (PlayerLevelEnum::cases() as $case) {
            $level = PlayerLevel::fromPlayerLevelEnum($case);
            $this->assertSame($case, $level->value());
        }
    }

    public function test_all_levels_are_defined(): void
    {
        $values = array_column(PlayerLevelEnum::cases(), 'value');

        $this->assertContains('beginner', $values);
        $this->assertContains('intermediate', $values);
        $this->assertContains('advanced', $values);
        $this->assertContains('confirmed', $values);
        $this->assertContains('expert', $values);
        $this->assertCount(5, $values);
    }
}
