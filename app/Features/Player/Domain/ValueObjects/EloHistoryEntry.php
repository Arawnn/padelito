<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

final readonly class EloHistoryEntry
{
    private function __construct(
        public string $playerId,
        public string $matchId,
        public string $team,
        public bool $won,
        public int $eloBefore,
        public int $eloAfter,
        public int $eloChange,
    ) {}

    public static function from(
        string $playerId,
        string $matchId,
        string $team,
        bool $won,
        int $eloBefore,
        int $eloAfter,
        int $eloChange,
    ): self {
        return new self($playerId, $matchId, $team, $won, $eloBefore, $eloAfter, $eloChange);
    }
}
