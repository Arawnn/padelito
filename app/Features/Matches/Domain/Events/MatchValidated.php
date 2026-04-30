<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class MatchValidated extends DomainEvent
{
    /**
     * @param  list<string>  $teamAPlayerIds
     * @param  list<string>  $teamBPlayerIds
     */
    public function __construct(
        public readonly string $matchId,
        public readonly array $teamAPlayerIds,
        public readonly array $teamBPlayerIds,
        public readonly int $teamAScore,
        public readonly int $teamBScore,
        public readonly bool $ranked,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(aggregateId: $matchId, eventId: $eventId, occurredOn: $occurredOn);
    }

    public static function eventName(): string
    {
        return 'match.validated';
    }

    public function toPrimitives(): array
    {
        return [
            'matchId' => $this->matchId,
            'teamAPlayerIds' => $this->teamAPlayerIds,
            'teamBPlayerIds' => $this->teamBPlayerIds,
            'teamAScore' => $this->teamAScore,
            'teamBScore' => $this->teamBScore,
            'ranked' => $this->ranked,
        ];
    }
}
