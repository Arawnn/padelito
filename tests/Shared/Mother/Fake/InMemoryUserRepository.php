<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $store = [];

    public function save(User $user): void
    {
        $this->store[$user->id()->value()] = $user;
    }

    public function findById(Id $id): ?User
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->store as $user) {
            if ($user->email()->value() === $email->value()) {
                return $user;
            }
        }

        return null;
    }
}
