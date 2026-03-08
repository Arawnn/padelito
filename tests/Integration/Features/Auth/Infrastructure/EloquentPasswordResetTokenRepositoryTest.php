<?php

namespace Tests\Integration\Features\Auth\Infrastructure;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Infrastructure\Repositories\EloquentPasswordResetTokenRepository;
use Tests\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EloquentPasswordResetTokenRepositoryTest extends IntegrationTestCase
{
    private EloquentPasswordResetTokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EloquentPasswordResetTokenRepository::class);
    }

    public function testItCreatesATokenAndValidatesIt(): void
    {
        $email = Email::fromString('john@example.com');
        $token = $this->repository->create($email);

        $this->assertTrue($this->repository->isValid($email, $token));
    }

    public function testItReturnsFalseForInvalidToken(): void
    {
        $email = Email::fromString('john@example.com');
        $this->repository->create($email);

        $this->assertFalse($this->repository->isValid($email, 'wrong-token'));
    }

    public function testItReturnsFalseWhenNoTokenExists(): void
    {
        $email = Email::fromString('john@example.com');
        $this->assertFalse($this->repository->isValid($email, 'any-token'));
    }

    public function testItDeletesAToken(): void
    {
        $email = Email::fromString('john@example.com');
        $token = $this->repository->create($email);

        $this->repository->delete($email);

        $this->assertFalse($this->repository->isValid($email, $token));
    }

    public function testItOverwritesExistingTokenOnCreate(): void
    {
        $email = Email::fromString('john@example.com');
        $this->repository->create($email);
        $newToken = $this->repository->create($email);

        $this->assertTrue($this->repository->isValid($email, $newToken));
    }
}
