<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Services;

use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Username;

final readonly class UsernameGeneratorService
{
    private const MAX_LENGTH = 30;

    private const MAX_BASE_LENGTH = 27; // reserves room for "_99"

    private const MAX_ATTEMPTS = 99;

    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function generateFrom(string $fullName): Username
    {
        $base = $this->normalize($fullName);

        if ($base === '') {
            $base = 'player';
        }

        $candidate = Username::fromString($base);

        if (! $this->playerRepository->findByUsername($candidate)) {
            return $candidate;
        }

        for ($i = 1; $i <= self::MAX_ATTEMPTS; $i++) {
            $candidate = Username::fromString($base.'_'.$i);

            if (! $this->playerRepository->findByUsername($candidate)) {
                return $candidate;
            }
        }

        return Username::fromString(substr($base, 0, 23).'_'.substr(uniqid(), -6));
    }

    private function normalize(string $fullName): string
    {
        // Transliterate accented characters (é -> e, ü -> u, etc.)
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fullName);
        $ascii = $ascii !== false ? $ascii : $fullName;

        // Lowercase, replace whitespace with underscore
        $normalized = strtolower(preg_replace('/\s+/', '_', trim($ascii)));

        // Remove anything that is not a-z, 0-9 or _
        $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized);

        // Collapse consecutive underscores and trim leading/trailing ones
        $normalized = trim(preg_replace('/_+/', '_', $normalized), '_');

        return substr($normalized, 0, self::MAX_BASE_LENGTH);
    }
}
