<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Repositories;

use App\Features\Auth\Domain\ValueObjects\Email;

interface PasswordResetTokenRepositoryInterface
{
    public function create(Email $email): string;

    public function isValid(Email $email, string $token): bool;

    public function delete(Email $email): void;
}
