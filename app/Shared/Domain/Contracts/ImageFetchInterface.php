<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface ImageFetchInterface
{
    /**
     * Download image bytes from an HTTPS URL (redirects followed with safety checks).
     *
     * @throws \App\Shared\Domain\Exceptions\ImageFetchFailedException
     */
    public function fetch(string $url): string;
}
