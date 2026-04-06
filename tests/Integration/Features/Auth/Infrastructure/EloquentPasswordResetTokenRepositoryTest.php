<?php

namespace Tests\Integration\Features\Auth\Infrastructure;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Infrastructure\Persistence\Eloquent\Repositories\EloquentPasswordResetTokenRepository;
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

    public function test_it_creates_a_token_and_validates_it(): void
    {
        $email = Email::fromString('john@example.com');
        $token = $this->repository->create($email);

        $this->assertTrue($this->repository->isValid($email, $token));
    }

    public function test_it_returns_false_for_invalid_token(): void
    {
        $email = Email::fromString('john@example.com');
        $this->repository->create($email);

        $this->assertFalse($this->repository->isValid($email, 'wrong-token'));
    }

    public function test_it_returns_false_when_no_token_exists(): void
    {
        $email = Email::fromString('john@example.com');
        $this->assertFalse($this->repository->isValid($email, 'any-token'));
    }

    public function test_it_deletes_a_token(): void
    {
        $email = Email::fromString('john@example.com');
        $token = $this->repository->create($email);

        $this->repository->delete($email);

        $this->assertFalse($this->repository->isValid($email, $token));
    }

    public function test_it_overwrites_existing_token_on_create(): void
    {
        $email = Email::fromString('john@example.com');
        $this->repository->create($email);
        $newToken = $this->repository->create($email);

        $this->assertTrue($this->repository->isValid($email, $newToken));
    }
}
