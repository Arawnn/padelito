<?php

namespace App\Features\Auth\Domain\Repositories;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;

interface UserRepositoryInterface
{
    public function findByEmail(Email $email): ?User;

    public function findById(Id $id): ?User;

    public function save(User $user): void;
}
