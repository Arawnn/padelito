<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Contracts;

use App\Features\Player\Application\Dto\MatchEloInput;
use App\Features\Player\Application\Dto\MatchEloSummary;

interface MatchEloSummaryReader
{
    public function forMatch(MatchEloInput $input, ?string $currentUserId): ?MatchEloSummary;

    /**
     * @param  list<MatchEloInput>  $inputs
     * @return array<string, MatchEloSummary>
     */
    public function summariesForMatches(array $inputs, ?string $currentUserId): array;
}
