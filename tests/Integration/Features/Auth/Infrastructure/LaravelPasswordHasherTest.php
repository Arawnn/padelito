<?php

namespace Tests\Integration\Features\Auth\Infrastructure;

use App\Features\Auth\Domain\ValueObjects\Password;
use App\Features\Auth\Infrastructure\Security\LaravelPasswordHasher;
use Tests\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class LaravelPasswordHasherTest extends IntegrationTestCase
{
    private LaravelPasswordHasher $hasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hasher = app(LaravelPasswordHasher::class);
    }

    public function testItHashesAPassword(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertNotEquals('Password123!', $hashed->value());
        $this->assertNotEmpty($hashed->value());
    }

    public function testItVerifiesACorrectPassword(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertTrue(
            $this->hasher->verify(Password::forVerification('Password123!'), $hashed)
        );
    }

    public function testItReturnsFalseForWrongPassword(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertFalse(
            $this->hasher->verify(Password::forVerification('WrongPassword!'), $hashed)
        );
    }
}
