<?php

declare(strict_types=1);

namespace Tests\Shared\Mother;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;

final class UserMother
{
    public static function register(): User
    {
        return User::register(
            id: Id::fromString('id-fixe-test'),
            name: Name::fromString('John Doe'),
            email: Email::fromString('john.doe@example.com'),
            password: HashedPassword::fromHash('hash-fake-pour-test')
        );
    }

    public static function reconstitute(): User
    {
        return User::reconstitute(
            id: Id::fromString('id-fixe-test'),
            name: Name::fromString('John Doe'),
            email: Email::fromString('john.doe@example.com'),
            password: HashedPassword::fromHash('hash-fake-pour-test')
        );
    }

    public static function withId(Id $id): User
    {
        return User::reconstitute(
            id: $id,
            name: Name::fromString('John Doe'),
            email: Email::fromString('john.doe@example.com'),
            password: HashedPassword::fromHash('hash-fake-pour-test')
        );
    }

    public static function withEmail(Email $email): User
    {
        return User::reconstitute(
            id: Id::fromString('id-fixe-test'),
            name: Name::fromString('John Doe'),
            email: $email,
            password: HashedPassword::fromHash('hash-fake-pour-test')
        );
    }

    public static function withPassword(HashedPassword $password): User
    {
        return User::reconstitute(
            id: Id::fromString('id-fixe-test'),
            name: Name::fromString('John Doe'),
            email: Email::fromString('john.doe@example.com'),
            password: $password
        );
    }

    public static function withName(Name $name): User
    {
        return User::reconstitute(
            id: Id::fromString('id-fixe-test'),
            name: $name,
            email: Email::fromString('john.doe@example.com'),
            password: HashedPassword::fromHash('hash-fake-pour-test')
        );
    }   
}
