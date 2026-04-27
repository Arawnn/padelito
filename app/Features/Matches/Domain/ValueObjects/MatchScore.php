<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final class MatchScore
{
    private function __construct(
        private ?Score $teamAScore,
        private ?Score $teamBScore,
        private ?SetsDetail $setsDetail,
        private SetsToWin $setsToWin,
    ) {}

    public static function empty(SetsToWin $setsToWin): self
    {
        return new self(null, null, null, $setsToWin);
    }

    public static function reconstitute(
        ?Score $teamAScore,
        ?Score $teamBScore,
        ?SetsDetail $setsDetail,
        SetsToWin $setsToWin,
    ): self {
        return new self($teamAScore, $teamBScore, $setsDetail, $setsToWin);
    }

    public function withSetsDetail(?SetsDetail $setsDetail): self
    {
        $clone = clone $this;
        $clone->setsDetail = $setsDetail;

        return $clone;
    }

    public function withSetsToWin(SetsToWin $setsToWin): self
    {
        $clone = clone $this;
        $clone->setsToWin = $setsToWin;

        return $clone;
    }

    public function withFinalizedScores(): self
    {
        [$a, $b] = $this->derivedScores();
        $clone = clone $this;
        $clone->teamAScore = Score::fromInt($a);
        $clone->teamBScore = Score::fromInt($b);

        return $clone;
    }

    public function teamAScore(): ?Score
    {
        return $this->teamAScore;
    }

    public function teamBScore(): ?Score
    {
        return $this->teamBScore;
    }

    public function setsDetail(): ?SetsDetail
    {
        return $this->setsDetail;
    }

    public function setsToWin(): SetsToWin
    {
        return $this->setsToWin;
    }

    /** @return array{0: int, 1: int} */
    public function derivedScores(): array
    {
        if ($this->setsDetail === null) {
            return [0, 0];
        }

        return [$this->setsDetail->teamASetsWon(), $this->setsDetail->teamBSetsWon()];
    }
}
