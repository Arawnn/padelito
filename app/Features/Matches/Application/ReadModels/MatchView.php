<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\ReadModels;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\ReadModels\MatchEloSummary;

final readonly class MatchView
{
    public function __construct(
        public PadelMatch $match,
        public ?MatchEloSummary $elo,
    ) {}
}
