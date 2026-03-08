<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class InvalidResetTokenExceptionTest extends TestCase
{
    public function test_it_creates_expired_or_invalid_exception(): void
    {
        $exception = InvalidResetTokenException::expiredOrInvalid();

        $this->assertNotEmpty($exception->getMessage());
        $this->assertStringContainsString('expired', strtolower($exception->getMessage()));
        $this->assertSame('INVALID_RESET_TOKEN', $exception->getDomainCode());
    }
}
