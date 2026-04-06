<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Infrastructure\Persistence\Eloquent\Mappers\UserMapper;
use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User as EloquentUser;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserMapper $userMapper,
        private EloquentUser $user
    ) {}

    public function findByEmail(Email $email): ?User
    {
        $userModel = $this->user->where('email', $email->value())->first();

        return $userModel ? $this->userMapper->toDomain($userModel) : null;
    }

    public function findById(Id $id): ?User
    {
        $userModel = $this->user->where('id', $id->value())->first();

        return $userModel ? $this->userMapper->toDomain($userModel) : null;
    }

    public function save(User $user): void
    {
        $data = $this->userMapper->toPersistence($user);

        EloquentUser::updateOrCreate(
            ['id' => $data['id']],
            $data,
        );
    }
}
