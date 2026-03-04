<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Repositories;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Infrastructure\Mappers\UserMapper;
use App\Features\Auth\Infrastructure\Models\User as UserModel;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserMapper $userMapper,
        private UserModel $userModel
    ) {}

    public function findByEmail(Email $email): ?User
    {
        $userModel = $this->userModel->where('email', $email->value())->first();

        return $userModel ? $this->userMapper->toDomain($userModel) : null;
    }

    public function findById(Id $id): ?User
    {
        $userModel = $this->userModel->where('id', $id->value())->first();

        return $userModel ? $this->userMapper->toDomain($userModel) : null;
    }

    public function create(User $user): void
    {
        $this->userMapper->toModel($user)->save();
    }

    public function update(User $user): void
    {
        $userModelAttributes = $this->userMapper->toModel($user)->getAttributes();
        $this->userModel->where('id', $user->id()->value())->update($userModelAttributes);
    }
}
