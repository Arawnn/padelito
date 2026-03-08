<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Password;

final class FakePasswordHasher implements PasswordHasherInterface
{
    public function hash(Password $password): HashedPassword
    {
        return HashedPassword::fromHash('hashed_'.$password->value());
    }

    public function verify(Password $password, HashedPassword $hashed): bool
    {
        return $hashed->value() === 'hashed_'.$password->value();
    }
}
