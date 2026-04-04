<?php

namespace Tests\Integration\Features\Auth\Infrastructure;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Features\Auth\Infrastructure\Repositories\EloquentUserRepository;
use Tests\IntegrationTestCase;
use Tests\Shared\Mother\UserMother;

/**
 * @internal
 *
 * @coversNothing
 */
final class EloquentUserRepositoryTest extends IntegrationTestCase
{
    private EloquentUserRepository $repository;

    private PasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EloquentUserRepository::class);
        $this->passwordHasher = app(PasswordHasherInterface::class);
    }

    public function test_it_persists_and_retrieves_a_user_by_id(): void
    {
        $user = UserMother::create()->registered()->build();
        $this->repository->save($user);

        $found = $this->repository->findById($user->id());

        $this->assertNotNull($found);
        $this->assertEquals($user->id()->value(), $found->id()->value());
        $this->assertEquals($user->email()->value(), $found->email()->value());
        $this->assertEquals($user->name()->value(), $found->name()->value());
    }

    public function test_it_persists_and_retrieves_a_user_by_email(): void
    {
        $user = UserMother::create()->registered()->build();
        $this->repository->save($user);

        $found = $this->repository->findByEmail($user->email());

        $this->assertNotNull($found);
        $this->assertEquals($user->id()->value(), $found->id()->value());
    }

    public function test_it_returns_null_when_user_not_found_by_id(): void
    {
        $found = $this->repository->findById(Id::fromString('non-existent-id'));
        $this->assertNull($found);
    }

    public function test_it_returns_null_when_user_not_found_by_email(): void
    {
        $found = $this->repository->findByEmail(Email::fromString('nobody@example.com'));
        $this->assertNull($found);
    }

    public function test_it_updates_a_user(): void
    {
        $user = UserMother::create()->registered()->build();
        $this->repository->save($user);

        $newPassword = Password::fromPlainText('NewPassword123!');
        $newPassword = $this->passwordHasher->hash($newPassword);
        $user->updatePassword($newPassword);
        $this->repository->update($user);

        $found = $this->repository->findById($user->id());
        $this->assertEquals($newPassword->value(), $found->password()->value());
    }
}
