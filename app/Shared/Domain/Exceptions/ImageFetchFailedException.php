<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

final class ImageFetchFailedException extends DomainException
{
    /**
     * @param  array<string, mixed>  $meta
     */
    private function __construct(
        string $message,
        string $domainCode,
        array $meta = [],
    ) {
        parent::__construct($message, $domainCode, $meta);
    }

    public static function because(string $reason): self
    {
        return new self(
            $reason,
            'IMAGE_FETCH_FAILED',
            ['violations' => [$reason]],
        );
    }

    protected function getDefaultCode(): string
    {
        return 'IMAGE_FETCH_FAILED';
    }
}
