<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Domain\Contracts\MailerInterface;

final class SpyMailer implements MailerInterface
{
    private array $sent = [];

    public function to(string $to, string $subject, string $token): void
    {
        $this->sent[] = compact('to', 'subject', 'token');
    }

    public function wasSentTo(string $email): bool
    {
        foreach ($this->sent as $mail) {
            if ($mail['to'] === $email) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->sent);
    }
}
