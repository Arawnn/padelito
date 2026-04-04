<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerProfileAlreadyExistException extends DomainException
{
    /**
     * @param  array<int, string>  $violations
     */
    private function __construct(
        private readonly array $violations
    ) {
        parent::__construct(
            implode(', ', $violations),
            domainCode: 'PLAYER_PROFILE_ALREADY_EXIST',
            meta: ['violations' => $violations]
        );
    }

    /**
     * @param  array<int, string>  $violations
     */
    public static function fromViolations(array $violations): self
    {
        return new self($violations);
    }

    public static function fromUsername(string $username): self
    {
        return new self([$username]);
    }

    public static function fromUserId(string $userId): self
    {
        return new self([$userId]);
    }

    /**
     * @return array<int, string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    protected function getDefaultCode(): string
    {
        return 'INVALID_USERNAME';
    }
}
