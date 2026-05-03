<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Contracts;

use App\Features\Matches\Application\Dto\MatchEloImpactInput;
use App\Features\Matches\Application\QueryResults\EloImpact;

interface MatchEloImpactReader
{
    public function forMatch(MatchEloImpactInput $input, ?string $currentUserId): ?EloImpact;

    /**
     * @param  list<MatchEloImpactInput>  $inputs
     * @return array<string, EloImpact>
     */
    public function forMatches(array $inputs, ?string $currentUserId): array;
}
