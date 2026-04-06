<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Persistence\Eloquent\Mappers;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Infrastructure\Persistence\Eloquent\User as UserModel;

final readonly class UserMapper
{
    public function toDomain(UserModel $userModel): User
    {
        return User::reconstitute(
            id: Id::fromString($userModel->id),
            name: Name::fromString($userModel->name),
            email: Email::fromString($userModel->email),
            password: HashedPassword::fromHash($userModel->password),
        );
    }

    public function toModel(User $user): UserModel
    {
        $model = new UserModel;
        $model->forceFill([
            'id' => $user->id()->value(),
            'name' => $user->name()->value(),
            'email' => $user->email()->value(),
            'password' => $user->password()->value(),
        ]);

        return $model;
    }
}
