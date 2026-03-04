<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Security;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Password;
use Illuminate\Contracts\Hashing\Hasher;

final class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private Hasher $hasher
    ) {}

    public function hash(Password $password): HashedPassword
    {
        return HashedPassword::fromHash($this->hasher->make($password->value()));
    }

    public function verify(Password $password, HashedPassword $hashedPassword): bool
    {
        return $this->hasher->check($password->value(), $hashedPassword->value());
    }
}
