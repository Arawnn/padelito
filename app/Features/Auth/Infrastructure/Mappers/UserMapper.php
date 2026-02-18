<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Mappers;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Infrastructure\Models\User as UserModel;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;

final readonly class UserMapper {
   
    public function toDomain(UserModel $userModel): User
    {
        return new User(
            id: Id::fromString($userModel->id),
            name: Name::fromString($userModel->name),
            email: Email::fromString($userModel->email),
            password: Password::fromHash($userModel->password),
        );
    }

    public function toModel(User $user): UserModel
    {
        $model = new UserModel();
        $model->forceFill([
            'id'       => $user->id()->value(),
            'name'     => $user->name()->value(),
            'email'    => $user->email()->value(),
            'password' => $user->password()->value(),
        ]);
        return $model;
    }
}