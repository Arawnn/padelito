<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UserAlreadyExistExceptionTest extends TestCase
{
    public function testItCreatesExceptionFromEmail(): void
    {
        $email = Email::fromString('john.doe@example.com');

        $exception = UserAlreadyExistException::fromEmail($email);

        $this->assertStringContainsString('john.doe@example.com', $exception->getMessage());
    }

    public function testItCreatesExceptionFromId(): void
    {
        $id = Id::fromString('user-123');

        $exception = UserAlreadyExistException::fromId($id);

        $this->assertStringContainsString('user-123', $exception->getMessage());
    }
}
