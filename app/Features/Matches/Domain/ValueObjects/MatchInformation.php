<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use DateTimeImmutable;

final class MatchInformation
{
    private function __construct(
        private ?CourtName $courtName,
        private ?Notes $notes,
        private ?DateTimeImmutable $matchDate,
    ) {}

    public static function empty(): self
    {
        return new self(null, null, null);
    }

    public static function reconstitute(
        ?CourtName $courtName,
        ?Notes $notes,
        ?DateTimeImmutable $matchDate,
    ): self {
        return new self($courtName, $notes, $matchDate);
    }

    public function withCourtName(?CourtName $courtName): self
    {
        $clone = clone $this;
        $clone->courtName = $courtName;

        return $clone;
    }

    public function withNotes(?Notes $notes): self
    {
        $clone = clone $this;
        $clone->notes = $notes;

        return $clone;
    }

    public function withMatchDate(?DateTimeImmutable $matchDate): self
    {
        $clone = clone $this;
        $clone->matchDate = $matchDate;

        return $clone;
    }

    public function courtName(): ?CourtName
    {
        return $this->courtName;
    }

    public function notes(): ?Notes
    {
        return $this->notes;
    }

    public function matchDate(): ?DateTimeImmutable
    {
        return $this->matchDate;
    }
}
