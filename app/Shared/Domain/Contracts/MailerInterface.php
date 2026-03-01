<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface MailerInterface 
{
    public function to(string $to, string $subject, string $token): void;
}