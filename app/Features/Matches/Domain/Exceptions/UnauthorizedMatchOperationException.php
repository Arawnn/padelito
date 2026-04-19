<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class UnauthorizedMatchOperationException extends DomainException
{
    private function __construct()
    {
        parent::__construct('You are not authorized to perform this operation on the match.', domainCode: 'UNAUTHORIZED_MATCH_OPERATION');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'UNAUTHORIZED_MATCH_OPERATION';
    }
}
