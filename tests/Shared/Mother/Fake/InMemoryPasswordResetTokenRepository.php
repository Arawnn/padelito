<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;

final class InMemoryPasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    private array $tokens = [];

    public function create(Email $email): string
    {
        $token = 'token_'.$email->value();
        $this->tokens[$email->value()] = $token;

        return $token;
    }

    public function isValid(Email $email, string $token): bool
    {
        return ($this->tokens[$email->value()] ?? null) === $token;
    }

    public function delete(Email $email): void
    {
        unset($this->tokens[$email->value()]);
    }
}
