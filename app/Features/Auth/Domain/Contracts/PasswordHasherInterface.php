<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Contracts;

use App\Features\Auth\Domain\ValueObjects\Password;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;

interface PasswordHasherInterface {
    public function hash(Password $password): HashedPassword;
    public function verify(Password $password, HashedPassword $hashedPassword): bool;
}