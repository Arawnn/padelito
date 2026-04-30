<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

final class MatchConfiguration
{
    private function __construct(
        private MatchType $type,
        private MatchFormat $format,
    ) {}

    public static function from(MatchType $type, MatchFormat $format): self
    {
        return new self($type, $format);
    }

    public function withType(MatchType $type): self
    {
        $clone = clone $this;
        $clone->type = $type;

        return $clone;
    }

    public function withFormat(MatchFormat $format): self
    {
        $clone = clone $this;
        $clone->format = $format;

        return $clone;
    }

    public function type(): MatchType
    {
        return $this->type;
    }

    public function format(): MatchFormat
    {
        return $this->format;
    }

    public function requiredPlayerCount(): int
    {
        return $this->format->requiredPlayerCount();
    }

    public function isSingles(): bool
    {
        return $this->format->isSingles();
    }
}
