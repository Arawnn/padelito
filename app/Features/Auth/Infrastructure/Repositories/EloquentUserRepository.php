<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Repositories;

use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Infrastructure\Models\User as UserModel;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Infrastructure\Mappers\UserMapper;

final readonly class EloquentUserRepository implements UserRepositoryInterface {
    public function __construct(
        private UserMapper $userMapper,
        private UserModel $userModel
    ) {}

    public function findByEmail(Email $email): ?User
    {
        return $this->userModel->where('email', $email)->first();
    }

    public function create(User $user): void
    {
        $this->userMapper->toModel($user)->save();
    }
}