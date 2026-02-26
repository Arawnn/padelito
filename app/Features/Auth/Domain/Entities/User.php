<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Entities;

use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Events\UserLoggedIn;
use App\Features\Auth\Domain\Events\UserLoggedOut;
use App\Features\Auth\Domain\Events\UserPasswordUpdated;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Shared\Domain\Entities\AggregateRoot;

final class User extends AggregateRoot {


    private function __construct(
        private Id $id,
        private Name $name,
        private Email $email,
        private HashedPassword $password,
    ) {
        parent::__construct();
    }

    public static function reconstitute(Id $id, Name $name, Email $email, HashedPassword $password): self
    {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            password: $password,
        );
    }

    public static function register(Id $id, Name $name, Email $email, HashedPassword $password): self
    {
        $user = new self(
            id: $id,
            name: $name,
            email: $email,
            password: $password,
        );
        $user->recordDomainEvent(new UserCreated($user));
        return $user;
    }

    public function login(): void
    {
        $this->recordDomainEvent(new UserLoggedIn($this));
    }

    public function logout(): void
    {
        $this->recordDomainEvent(new UserLoggedOut($this));
    }

    public function updatePassword(HashedPassword $password): void
    {
        $this->password = $password;
        $this->recordDomainEvent(new UserPasswordUpdated($this));
    }

    
    public function id(): Id { return $this->id; }
    public function name(): Name { return $this->name; }
    public function email(): Email { return $this->email; }
    public function password(): HashedPassword { return $this->password; }
}