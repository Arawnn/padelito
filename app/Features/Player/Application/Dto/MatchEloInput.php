<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Dto;

final readonly class MatchEloInput
{
    /**
     * @param  list<string>  $teamAPlayerIds
     * @param  list<string>  $teamBPlayerIds
     */
    public function __construct(
        public string $matchId,
        public bool $isRanked,
        public bool $isValidated,
        public array $teamAPlayerIds,
        public array $teamBPlayerIds,
        public ?int $teamAScore,
        public ?int $teamBScore,
    ) {}
}
