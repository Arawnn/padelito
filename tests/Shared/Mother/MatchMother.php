<?php

declare(strict_types=1);

namespace Tests\Shared\Mother;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchStatusEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;

final class MatchMother
{
    private string $id = '10000000-0000-0000-0000-000000000001';

    private string $creatorId = '00000000-0000-0000-0000-000000000001';

    private string $type = 'friendly';

    private string $format = 'doubles';

    private ?string $teamAPlayer2Id = null;

    private ?string $teamBPlayer1Id = null;

    private ?string $teamBPlayer2Id = null;

    private ?SetsDetail $setsDetail = null;

    private array $confirmedPlayerIds = [];

    private string $status = 'pending';

    private function __construct() {}

    public static function create(): self
    {
        return new self;
    }

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withCreator(string $creatorId): self
    {
        $clone = clone $this;
        $clone->creatorId = $creatorId;

        return $clone;
    }

    public function asRanked(): self
    {
        $clone = clone $this;
        $clone->type = 'ranked';

        return $clone;
    }

    public function asSingles(): self
    {
        $clone = clone $this;
        $clone->format = 'singles';

        return $clone;
    }

    public function withTeamAPlayer2(string $playerId): self
    {
        $clone = clone $this;
        $clone->teamAPlayer2Id = $playerId;

        return $clone;
    }

    public function withTeamBPlayer1(string $playerId): self
    {
        $clone = clone $this;
        $clone->teamBPlayer1Id = $playerId;

        return $clone;
    }

    public function withTeamBPlayer2(string $playerId): self
    {
        $clone = clone $this;
        $clone->teamBPlayer2Id = $playerId;

        return $clone;
    }

    public function withSetsDetail(SetsDetail $setsDetail): self
    {
        $clone = clone $this;
        $clone->setsDetail = $setsDetail;

        return $clone;
    }

    public function withConfirmedPlayerIds(array $ids): self
    {
        $clone = clone $this;
        $clone->confirmedPlayerIds = $ids;

        return $clone;
    }

    public function withStatus(string $status): self
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function withFullDoublesLineup(string $p1a = '00000000-0000-0000-0000-000000000001', string $p2a = '00000000-0000-0000-0000-000000000002', string $p1b = '00000000-0000-0000-0000-000000000003', string $p2b = '00000000-0000-0000-0000-000000000004'): self
    {
        return $this
            ->withCreator($p1a)
            ->withTeamAPlayer2($p2a)
            ->withTeamBPlayer1($p1b)
            ->withTeamBPlayer2($p2b);
    }

    public function build(): PadelMatch
    {
        return PadelMatch::reconstitute(
            id: MatchId::fromString($this->id),
            type: MatchType::fromEnum(MatchTypeEnum::from($this->type)),
            format: MatchFormat::fromEnum(MatchFormatEnum::from($this->format)),
            status: MatchStatus::fromEnum(MatchStatusEnum::from($this->status)),
            createdBy: PlayerId::fromString($this->creatorId),
            teamAPlayer1Id: PlayerId::fromString($this->creatorId),
            teamAPlayer2Id: $this->teamAPlayer2Id ? PlayerId::fromString($this->teamAPlayer2Id) : null,
            teamBPlayer1Id: $this->teamBPlayer1Id ? PlayerId::fromString($this->teamBPlayer1Id) : null,
            teamBPlayer2Id: $this->teamBPlayer2Id ? PlayerId::fromString($this->teamBPlayer2Id) : null,
            setsDetail: $this->setsDetail,
            teamAScore: null,
            teamBScore: null,
            courtName: null,
            notes: null,
            teamAEloBefore: null,
            teamBEloBefore: null,
            eloChange: null,
            setsToWin: SetsToWin::fromInt(2),
            matchDate: null,
            confirmedPlayerIds: array_map(fn (string $id) => PlayerId::fromString($id), $this->confirmedPlayerIds),
        );
    }
}
