<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Entities;

use App\Features\Matches\Domain\Events\MatchCancelled;
use App\Features\Matches\Domain\Events\MatchConfirmationsReset;
use App\Features\Matches\Domain\Events\MatchCreated;
use App\Features\Matches\Domain\Events\MatchPlayerConfirmed;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Exceptions\CannotSwitchToSinglesWithMultiplePlayersException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchNotReadyForConfirmationException;
use App\Features\Matches\Domain\Exceptions\PlayerAlreadyConfirmedException;
use App\Features\Matches\Domain\Exceptions\PlayerNotParticipantException;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\EloChange;
use App\Features\Matches\Domain\ValueObjects\EloRating;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\Score;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use App\Shared\Domain\Entities\AggregateRoot;
use DateTimeImmutable;

final class PadelMatch extends AggregateRoot
{
    /** @param list<PlayerId> $confirmedPlayerIds */
    private function __construct(
        private readonly MatchId $id,
        private MatchType $type,
        private MatchFormat $format,
        private MatchStatus $status,
        private readonly PlayerId $createdBy,
        private PlayerId $teamAPlayer1Id,
        private ?PlayerId $teamAPlayer2Id,
        private ?PlayerId $teamBPlayer1Id,
        private ?PlayerId $teamBPlayer2Id,
        private ?SetsDetail $setsDetail,
        private ?Score $teamAScore,
        private ?Score $teamBScore,
        private ?CourtName $courtName,
        private ?Notes $notes,
        private ?EloRating $teamAEloBefore,
        private ?EloRating $teamBEloBefore,
        private ?EloChange $eloChange,
        private SetsToWin $setsToWin,
        private ?DateTimeImmutable $matchDate,
        private array $confirmedPlayerIds,
    ) {}

    public static function create(
        MatchId $id,
        MatchType $type,
        MatchFormat $format,
        PlayerId $createdBy,
        ?CourtName $courtName = null,
        ?DateTimeImmutable $matchDate = null,
        ?Notes $notes = null,
        ?SetsToWin $setsToWin = null,
    ): self {
        $padelMatch = new self(
            id: $id,
            type: $type,
            format: $format,
            status: MatchStatus::pending(),
            createdBy: $createdBy,
            teamAPlayer1Id: $createdBy,
            teamAPlayer2Id: null,
            teamBPlayer1Id: null,
            teamBPlayer2Id: null,
            setsDetail: null,
            teamAScore: null,
            teamBScore: null,
            courtName: $courtName,
            notes: $notes,
            teamAEloBefore: null,
            teamBEloBefore: null,
            eloChange: null,
            setsToWin: $setsToWin ?? SetsToWin::fromInt(2),
            matchDate: $matchDate,
            confirmedPlayerIds: [],
        );

        $padelMatch->recordDomainEvent(new MatchCreated($id->value(), $createdBy->value()));

        return $padelMatch;
    }

    /** @param list<PlayerId> $confirmedPlayerIds */
    public static function reconstitute(
        MatchId $id,
        MatchType $type,
        MatchFormat $format,
        MatchStatus $status,
        PlayerId $createdBy,
        PlayerId $teamAPlayer1Id,
        ?PlayerId $teamAPlayer2Id,
        ?PlayerId $teamBPlayer1Id,
        ?PlayerId $teamBPlayer2Id,
        ?SetsDetail $setsDetail,
        ?Score $teamAScore,
        ?Score $teamBScore,
        ?CourtName $courtName,
        ?Notes $notes,
        ?EloRating $teamAEloBefore,
        ?EloRating $teamBEloBefore,
        ?EloChange $eloChange,
        SetsToWin $setsToWin,
        ?DateTimeImmutable $matchDate,
        array $confirmedPlayerIds,
    ): self {
        return new self(
            id: $id,
            type: $type,
            format: $format,
            status: $status,
            createdBy: $createdBy,
            teamAPlayer1Id: $teamAPlayer1Id,
            teamAPlayer2Id: $teamAPlayer2Id,
            teamBPlayer1Id: $teamBPlayer1Id,
            teamBPlayer2Id: $teamBPlayer2Id,
            setsDetail: $setsDetail,
            teamAScore: $teamAScore,
            teamBScore: $teamBScore,
            courtName: $courtName,
            notes: $notes,
            teamAEloBefore: $teamAEloBefore,
            teamBEloBefore: $teamBEloBefore,
            eloChange: $eloChange,
            setsToWin: $setsToWin,
            matchDate: $matchDate,
            confirmedPlayerIds: $confirmedPlayerIds,
        );
    }

    /** @return list<PlayerId> */
    public function participantIds(): array
    {
        return array_values(array_filter([
            $this->teamAPlayer1Id,
            $this->teamAPlayer2Id,
            $this->teamBPlayer1Id,
            $this->teamBPlayer2Id,
        ]));
    }

    public function participantCount(): int
    {
        return count($this->participantIds());
    }

    public function isParticipant(PlayerId $playerId): bool
    {
        foreach ($this->participantIds() as $participant) {
            if ($participant->equals($playerId)) {
                return true;
            }
        }

        return false;
    }

    public function isCreator(PlayerId $playerId): bool
    {
        return $this->createdBy->equals($playerId);
    }

    public function isReadyForConfirmation(): bool
    {
        return $this->setsDetail !== null
            && $this->participantCount() === $this->format->requiredPlayerCount()
            && $this->setsDetail->hasWinner($this->setsToWin->value());
    }

    public function hasAllParticipantsConfirmed(): bool
    {
        $participantValues = array_map(fn (PlayerId $id) => $id->value(), $this->participantIds());
        $confirmedValues = array_map(fn (PlayerId $id) => $id->value(), $this->confirmedPlayerIds);
        sort($participantValues);
        sort($confirmedValues);

        return $participantValues === $confirmedValues;
    }

    /** @return array{0: int, 1: int} */
    public function derivedScores(): array
    {
        if ($this->setsDetail === null) {
            return [0, 0];
        }

        return [$this->setsDetail->teamASetsWon(), $this->setsDetail->teamBSetsWon()];
    }

    public function winningTeam(): ?Team
    {
        [$a, $b] = $this->derivedScores();

        if ($a > $b) {
            return Team::A();
        }

        if ($b > $a) {
            return Team::B();
        }

        return null;
    }

    public function assignPlayer(PlayerId $playerId, Team $team, int $position): void
    {
        if ($team->isA()) {
            if ($position === 2) {
                $this->teamAPlayer2Id = $playerId;
            }
        } else {
            if ($position === 1) {
                $this->teamBPlayer1Id = $playerId;
            } else {
                $this->teamBPlayer2Id = $playerId;
            }
        }

        $this->resetConfirmations();
    }

    public function confirm(PlayerId $playerId): void
    {
        if ($this->status->isValidated()) {
            throw MatchAlreadyValidatedException::create();
        }

        if ($this->status->isCancelled()) {
            throw MatchAlreadyCancelledException::create();
        }

        if (! $this->isReadyForConfirmation()) {
            throw MatchNotReadyForConfirmationException::create();
        }

        if (! $this->isParticipant($playerId)) {
            throw PlayerNotParticipantException::create();
        }

        foreach ($this->confirmedPlayerIds as $confirmed) {
            if ($confirmed->equals($playerId)) {
                throw PlayerAlreadyConfirmedException::create();
            }
        }

        $this->confirmedPlayerIds[] = $playerId;
        $this->recordDomainEvent(new MatchPlayerConfirmed($this->id->value(), $playerId->value()));

        if ($this->hasAllParticipantsConfirmed()) {
            $this->finalize();
        }
    }

    private function finalize(): void
    {
        [$a, $b] = $this->derivedScores();
        $this->teamAScore = Score::fromInt($a);
        $this->teamBScore = Score::fromInt($b);
        $this->status = MatchStatus::validated();
        $this->recordDomainEvent(new MatchValidated($this->id->value()));
    }

    public function recordEloSnapshot(EloRating $teamAEloBefore, EloRating $teamBEloBefore, EloChange $eloChange): void
    {
        $this->teamAEloBefore = $teamAEloBefore;
        $this->teamBEloBefore = $teamBEloBefore;
        $this->eloChange = $eloChange;
    }

    public function updateCourtName(?CourtName $courtName): void
    {
        $this->courtName = $courtName;
    }

    public function updateMatchDate(?DateTimeImmutable $matchDate): void
    {
        $this->matchDate = $matchDate;
    }

    public function updateNotes(?Notes $notes): void
    {
        $this->notes = $notes;
    }

    public function updateSetsDetail(?SetsDetail $setsDetail): void
    {
        $this->setsDetail = $setsDetail;
        $this->resetConfirmations();
    }

    public function updateSetsToWin(SetsToWin $setsToWin): void
    {
        $this->setsToWin = $setsToWin;
        $this->resetConfirmations();
    }

    public function updateFormat(MatchFormat $format): void
    {
        if ($format->isSingles() && $this->participantCount() >= 3) {
            throw CannotSwitchToSinglesWithMultiplePlayersException::create();
        }

        $changed = $this->format->value() !== $format->value();
        $this->format = $format;

        if ($changed) {
            $this->resetConfirmations();
        }
    }

    public function updateType(MatchType $type): void
    {
        $this->type = $type;
        $this->resetConfirmations();
    }

    private function resetConfirmations(): void
    {
        if (! empty($this->confirmedPlayerIds)) {
            $this->confirmedPlayerIds = [];
            $this->recordDomainEvent(new MatchConfirmationsReset($this->id->value()));
        }
    }

    public function cancel(): void
    {
        if ($this->status->isValidated()) {
            throw MatchAlreadyValidatedException::create();
        }

        if ($this->status->isCancelled()) {
            throw MatchAlreadyCancelledException::create();
        }

        $this->status = MatchStatus::cancelled();
        $this->recordDomainEvent(new MatchCancelled($this->id->value()));
    }

    public function id(): MatchId
    {
        return $this->id;
    }

    public function type(): MatchType
    {
        return $this->type;
    }

    public function format(): MatchFormat
    {
        return $this->format;
    }

    public function status(): MatchStatus
    {
        return $this->status;
    }

    public function createdBy(): PlayerId
    {
        return $this->createdBy;
    }

    public function teamAPlayer1Id(): PlayerId
    {
        return $this->teamAPlayer1Id;
    }

    public function teamAPlayer2Id(): ?PlayerId
    {
        return $this->teamAPlayer2Id;
    }

    public function teamBPlayer1Id(): ?PlayerId
    {
        return $this->teamBPlayer1Id;
    }

    public function teamBPlayer2Id(): ?PlayerId
    {
        return $this->teamBPlayer2Id;
    }

    public function setsDetail(): ?SetsDetail
    {
        return $this->setsDetail;
    }

    public function teamAScore(): ?Score
    {
        return $this->teamAScore;
    }

    public function teamBScore(): ?Score
    {
        return $this->teamBScore;
    }

    public function courtName(): ?CourtName
    {
        return $this->courtName;
    }

    public function notes(): ?Notes
    {
        return $this->notes;
    }

    public function teamAEloBefore(): ?EloRating
    {
        return $this->teamAEloBefore;
    }

    public function teamBEloBefore(): ?EloRating
    {
        return $this->teamBEloBefore;
    }

    public function eloChange(): ?EloChange
    {
        return $this->eloChange;
    }

    public function setsToWin(): SetsToWin
    {
        return $this->setsToWin;
    }

    public function matchDate(): ?DateTimeImmutable
    {
        return $this->matchDate;
    }

    /** @return list<PlayerId> */
    public function confirmedPlayerIds(): array
    {
        return $this->confirmedPlayerIds;
    }
}
