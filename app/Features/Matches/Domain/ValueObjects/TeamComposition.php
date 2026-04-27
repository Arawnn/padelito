<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final class TeamComposition
{
    private function __construct(
        private readonly PlayerId $creator,
        private ?PlayerId $partner,
        private ?PlayerId $opponent1,
        private ?PlayerId $opponent2,
    ) {}

    public static function withCreator(PlayerId $creator): self
    {
        return new self($creator, null, null, null);
    }

    public static function reconstitute(
        PlayerId $creator,
        ?PlayerId $partner,
        ?PlayerId $opponent1,
        ?PlayerId $opponent2,
    ): self {
        return new self($creator, $partner, $opponent1, $opponent2);
    }

    public function withPartner(PlayerId $partner): self
    {
        $clone = clone $this;
        $clone->partner = $partner;

        return $clone;
    }

    public function withOpponent1(PlayerId $opponent): self
    {
        $clone = clone $this;
        $clone->opponent1 = $opponent;

        return $clone;
    }

    public function withOpponent2(PlayerId $opponent): self
    {
        $clone = clone $this;
        $clone->opponent2 = $opponent;

        return $clone;
    }

    public function withoutPartner(): self
    {
        $clone = clone $this;
        $clone->partner = null;

        return $clone;
    }

    public function withoutOpponent1(): self
    {
        $clone = clone $this;
        $clone->opponent1 = null;

        return $clone;
    }

    public function withoutOpponent2(): self
    {
        $clone = clone $this;
        $clone->opponent2 = null;

        return $clone;
    }

    public function creator(): PlayerId
    {
        return $this->creator;
    }

    public function partner(): ?PlayerId
    {
        return $this->partner;
    }

    public function opponent1(): ?PlayerId
    {
        return $this->opponent1;
    }

    public function opponent2(): ?PlayerId
    {
        return $this->opponent2;
    }

    /** @return list<PlayerId> */
    public function participants(): array
    {
        return array_values(array_filter([
            $this->creator,
            $this->partner,
            $this->opponent1,
            $this->opponent2,
        ]));
    }

    public function participantCount(): int
    {
        return count($this->participants());
    }

    public function isParticipant(PlayerId $playerId): bool
    {
        foreach ($this->participants() as $participant) {
            if ($participant->equals($playerId)) {
                return true;
            }
        }

        return false;
    }

    public function isCreator(PlayerId $playerId): bool
    {
        return $this->creator->equals($playerId);
    }

    /** @return list<PlayerId> */
    public function teamAPlayerIds(): array
    {
        return array_values(array_filter([$this->creator, $this->partner]));
    }

    /** @return list<PlayerId> */
    public function teamBPlayerIds(): array
    {
        return array_values(array_filter([$this->opponent1, $this->opponent2]));
    }
}
