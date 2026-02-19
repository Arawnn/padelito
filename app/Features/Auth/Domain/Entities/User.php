<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Entities;

use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Entities\AggregateRoot;
use App\Features\Auth\Domain\Events\UserCreated;

final class User extends AggregateRoot {


    private function __construct(
        private Id $id,
        private Name $name,
        private Email $email,
        private Password $password,
    ) {
        parent::__construct();
    }

    public static function register(Id $id, Name $name, Email $email, Password $password): self
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

    
    public function id(): Id { return $this->id; }
    public function name(): Name { return $this->name; }
    public function email(): Email { return $this->email; }
    public function password(): Password { return $this->password; }
}