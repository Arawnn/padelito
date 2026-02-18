<?php

namespace App\Features\Auth\Domain\Repositories;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Email;

interface UserRepositoryInterface {
    public function findByEmail(Email $email): ?User;
    public function create(User $user): void;
}