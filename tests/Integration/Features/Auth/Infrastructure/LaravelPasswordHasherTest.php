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

    public function test_it_hashes_a_password(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertNotEquals('Password123!', $hashed->value());
        $this->assertNotEmpty($hashed->value());
    }

    public function test_it_verifies_a_correct_password(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertTrue(
            $this->hasher->verify(Password::forVerification('Password123!'), $hashed)
        );
    }

    public function test_it_returns_false_for_wrong_password(): void
    {
        $password = Password::fromPlainText('Password123!');
        $hashed = $this->hasher->hash($password);

        $this->assertFalse(
            $this->hasher->verify(Password::forVerification('WrongPassword!'), $hashed)
        );
    }
}
