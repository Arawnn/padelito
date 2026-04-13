<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\ValueObjects\ProfileVisibility;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProfileVisibilityTest extends TestCase
{
    public function test_public_factory_creates_public_visibility(): void
    {
        $visibility = ProfileVisibility::public();

        $this->assertTrue($visibility->isPublic());
        $this->assertFalse($visibility->isPrivate());
    }

    public function test_private_factory_creates_private_visibility(): void
    {
        $visibility = ProfileVisibility::private();

        $this->assertFalse($visibility->isPublic());
        $this->assertTrue($visibility->isPrivate());
    }

    public function test_from_bool_true_creates_public_visibility(): void
    {
        $visibility = ProfileVisibility::fromBool(true);

        $this->assertTrue($visibility->isPublic());
    }

    public function test_from_bool_false_creates_private_visibility(): void
    {
        $visibility = ProfileVisibility::fromBool(false);

        $this->assertTrue($visibility->isPrivate());
    }

    public function test_is_public_and_is_private_are_complementary(): void
    {
        $public = ProfileVisibility::public();
        $private = ProfileVisibility::private();

        $this->assertNotEquals($public->isPublic(), $private->isPublic());
        $this->assertNotEquals($public->isPrivate(), $private->isPrivate());
    }
}
