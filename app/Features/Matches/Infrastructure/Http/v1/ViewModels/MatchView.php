<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\ViewModels;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Player\Application\Dto\MatchEloSummary;

final readonly class MatchView
{
    public function __construct(
        public PadelMatch $match,
        public ?MatchEloSummary $elo,
    ) {}
}
