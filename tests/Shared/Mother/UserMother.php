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
    private string $id = '00000000-0000-0000-0000-000000000000';

    private string $name = 'John Doe';

    private string $email = 'john.doe@example.com';

    private string $hashedPassword = 'hash-fake-pour-test';

    private bool $useRegister = false;

    private function __construct() {}

    public static function create(): self
    {
        return new self;
    }

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withEmail(string $email): self
    {
        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    public function withHashedPassword(string $hashedPassword): self
    {
        $clone = clone $this;
        $clone->hashedPassword = $hashedPassword;

        return $clone;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function registered(): self
    {
        $clone = clone $this;
        $clone->useRegister = true;

        return $clone;
    }

    public function build(): User
    {
        $id = Id::fromString($this->id);
        $name = Name::fromString($this->name);
        $email = Email::fromString($this->email);
        $password = HashedPassword::fromHash($this->hashedPassword);

        if ($this->useRegister) {
            return User::register(id: $id, name: $name, email: $email, password: $password);
        }

        return User::reconstitute(id: $id, name: $name, email: $email, password: $password);
    }
}
