<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Exceptions\InvalidSetsDetailException;

final readonly class SetsDetail
{
    /** @param list<array{a: int, b: int}> $sets */
    private function __construct(private array $sets) {}

    /**
     * @param  list<array{a: int, b: int}>  $sets
     */
    public static function fromArray(array $sets): self
    {
        self::validate($sets);

        return new self($sets);
    }

    /** @return list<array{a: int, b: int}> */
    public function sets(): array
    {
        return $this->sets;
    }

    public function teamASetsWon(): int
    {
        return count(array_filter($this->sets, fn (array $set) => $set['a'] > $set['b']));
    }

    public function teamBSetsWon(): int
    {
        return count(array_filter($this->sets, fn (array $set) => $set['b'] > $set['a']));
    }

    public function setCount(): int
    {
        return count($this->sets);
    }

    public function hasWinner(int $setsToWin): bool
    {
        if ($setsToWin < 1 || $setsToWin > 3 || count($this->sets) > (2 * $setsToWin - 1)) {
            return false;
        }

        $teamASetsWon = 0;
        $teamBSetsWon = 0;

        foreach ($this->sets as $set) {
            if ($teamASetsWon === $setsToWin || $teamBSetsWon === $setsToWin) {
                return false;
            }

            if (self::isSuperTieBreakSet($set) && ($teamASetsWon !== $setsToWin - 1 || $teamBSetsWon !== $setsToWin - 1)) {
                return false;
            }

            if ($set['a'] > $set['b']) {
                $teamASetsWon++;
            } else {
                $teamBSetsWon++;
            }
        }

        return ($teamASetsWon === $setsToWin && $teamBSetsWon < $setsToWin)
            || ($teamBSetsWon === $setsToWin && $teamASetsWon < $setsToWin);
    }

    /** @param list<array{a: int, b: int}> $sets */
    private static function validate(array $sets): void
    {
        $violations = [];

        if (empty($sets)) {
            $violations[] = 'Sets detail cannot be empty';
        }

        if (count($sets) > 5) {
            $violations[] = 'A match cannot have more than 5 sets';
        }

        foreach ($sets as $i => $set) {
            if (! isset($set['a'], $set['b'])) {
                $violations[] = "Set {$i} must have keys 'a' and 'b'";

                continue;
            }

            if (! is_int($set['a']) || ! is_int($set['b'])) {
                $violations[] = "Set {$i} scores must be integers";

                continue;
            }

            if ($set['a'] < 0 || $set['b'] < 0) {
                $violations[] = "Set {$i} scores cannot be negative";

                continue;
            }

            if (! self::isClassicSet($set) && ! self::isValidSuperTieBreakSet($set, $i, count($sets))) {
                $violations[] = "Set {$i} has an unrealistic padel score";
            }
        }

        if (! empty($violations)) {
            throw InvalidSetsDetailException::fromViolations($violations);
        }
    }

    /** @param array{a: int, b: int} $set */
    private static function isClassicSet(array $set): bool
    {
        $winnerScore = max($set['a'], $set['b']);
        $loserScore = min($set['a'], $set['b']);

        return ($winnerScore === 6 && $loserScore <= 4)
            || ($winnerScore === 7 && ($loserScore === 5 || $loserScore === 6));
    }

    /** @param array{a: int, b: int} $set */
    private static function isValidSuperTieBreakSet(array $set, int $index, int $setCount): bool
    {
        if ($index !== $setCount - 1) {
            return false;
        }

        if (! self::isSuperTieBreakSet($set)) {
            return false;
        }

        $winnerScore = max($set['a'], $set['b']);
        $loserScore = min($set['a'], $set['b']);

        return $winnerScore === 10 ? $loserScore <= 8 : $loserScore === $winnerScore - 2;
    }

    /** @param array{a: int, b: int} $set */
    private static function isSuperTieBreakSet(array $set): bool
    {
        return max($set['a'], $set['b']) >= 10
            && abs($set['a'] - $set['b']) >= 2;
    }
}
