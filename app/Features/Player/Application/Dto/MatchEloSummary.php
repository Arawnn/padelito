<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Dto;

final readonly class MatchEloSummary
{
    private function __construct(
        public int $teamABefore,
        public int $teamBBefore,
        public int $teamAChange,
        public int $teamBChange,
        public ?int $currentUserChange,
        public string $source,
    ) {}

    public static function from(
        int $teamABefore,
        int $teamBBefore,
        int $teamAChange,
        int $teamBChange,
        ?int $currentUserChange,
        string $source,
    ): self {
        return new self(
            teamABefore: $teamABefore,
            teamBBefore: $teamBBefore,
            teamAChange: $teamAChange,
            teamBChange: $teamBChange,
            currentUserChange: $currentUserChange,
            source: $source,
        );
    }

    public function toArray(): array
    {
        return [
            'team_a_before' => $this->teamABefore,
            'team_b_before' => $this->teamBBefore,
            'team_a_change' => $this->teamAChange,
            'team_b_change' => $this->teamBChange,
            'current_user_change' => $this->currentUserChange,
            'source' => $this->source,
        ];
    }
}
