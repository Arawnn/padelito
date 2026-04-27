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
use App\Features\Matches\Domain\ValueObjects\EloSnapshot;
use App\Features\Matches\Domain\ValueObjects\MatchConfiguration;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInformation;
use App\Features\Matches\Domain\ValueObjects\MatchScore;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\Score;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use App\Features\Matches\Domain\ValueObjects\TeamComposition;
use App\Shared\Domain\Entities\AggregateRoot;
use DateTimeImmutable;

final class PadelMatch extends AggregateRoot
{
    /** @param list<PlayerId> $confirmedPlayerIds */
    private function __construct(
        private readonly MatchId $id,
        private MatchStatus $status,
        private TeamComposition $composition,
        private MatchConfiguration $configuration,
        private MatchScore $score,
        private MatchInformation $information,
        private ?EloSnapshot $eloSnapshot,
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
            status: MatchStatus::pending(),
            composition: TeamComposition::withCreator($createdBy),
            configuration: MatchConfiguration::from($type, $format),
            score: MatchScore::empty($setsToWin ?? SetsToWin::fromInt(2)),
            information: MatchInformation::reconstitute($courtName, $notes, $matchDate),
            eloSnapshot: null,
            confirmedPlayerIds: [],
        );

        $padelMatch->recordDomainEvent(new MatchCreated($id->value(), $createdBy->value()));

        return $padelMatch;
    }

    /** @param list<PlayerId> $confirmedPlayerIds */
    public static function reconstitute(
        MatchId $id,
        MatchStatus $status,
        PlayerId $creator,
        ?PlayerId $partner,
        ?PlayerId $opponent1,
        ?PlayerId $opponent2,
        MatchType $type,
        MatchFormat $format,
        ?Score $teamAScore,
        ?Score $teamBScore,
        ?SetsDetail $setsDetail,
        SetsToWin $setsToWin,
        ?CourtName $courtName,
        ?Notes $notes,
        ?DateTimeImmutable $matchDate,
        ?EloRating $teamAEloBefore,
        ?EloRating $teamBEloBefore,
        ?EloChange $eloChange,
        array $confirmedPlayerIds,
    ): self {
        $eloSnapshot = ($teamAEloBefore !== null && $teamBEloBefore !== null && $eloChange !== null)
            ? EloSnapshot::from($teamAEloBefore, $teamBEloBefore, $eloChange)
            : null;

        return new self(
            id: $id,
            status: $status,
            composition: TeamComposition::reconstitute($creator, $partner, $opponent1, $opponent2),
            configuration: MatchConfiguration::from($type, $format),
            score: MatchScore::reconstitute($teamAScore, $teamBScore, $setsDetail, $setsToWin),
            information: MatchInformation::reconstitute($courtName, $notes, $matchDate),
            eloSnapshot: $eloSnapshot,
            confirmedPlayerIds: $confirmedPlayerIds,
        );
    }

    /** @return list<PlayerId> */
    public function participantIds(): array
    {
        return $this->composition->participants();
    }

    public function participantCount(): int
    {
        return $this->composition->participantCount();
    }

    public function isParticipant(PlayerId $playerId): bool
    {
        return $this->composition->isParticipant($playerId);
    }

    public function isCreator(PlayerId $playerId): bool
    {
        return $this->composition->isCreator($playerId);
    }

    public function isReadyForConfirmation(): bool
    {
        return $this->score->setsDetail() !== null
            && $this->composition->participantCount() === $this->configuration->requiredPlayerCount()
            && $this->score->setsDetail()->hasWinner($this->score->setsToWin()->value());
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
        return $this->score->derivedScores();
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

    public function isTeamFull(Team $team): bool
    {
        if ($team->isA()) {
            return $this->composition->partner() !== null;
        }

        return $this->composition->opponent1() !== null && $this->composition->opponent2() !== null;
    }

    public function assignPlayer(PlayerId $playerId, Team $team): void
    {
        if ($team->isA()) {
            $this->composition = $this->composition->withPartner($playerId);
        } elseif ($this->composition->opponent1() === null) {
            $this->composition = $this->composition->withOpponent1($playerId);
        } else {
            $this->composition = $this->composition->withOpponent2($playerId);
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
        $this->score = $this->score->withFinalizedScores();
        $this->status = MatchStatus::validated();
        $this->recordDomainEvent(new MatchValidated($this->id->value()));
    }

    public function recordEloSnapshot(EloRating $teamABefore, EloRating $teamBBefore, EloChange $change): void
    {
        $this->eloSnapshot = EloSnapshot::from($teamABefore, $teamBBefore, $change);
    }

    public function updateCourtName(?CourtName $courtName): void
    {
        $this->information = $this->information->withCourtName($courtName);
    }

    public function updateMatchDate(?DateTimeImmutable $matchDate): void
    {
        $this->information = $this->information->withMatchDate($matchDate);
    }

    public function updateNotes(?Notes $notes): void
    {
        $this->information = $this->information->withNotes($notes);
    }

    public function updateSetsDetail(?SetsDetail $setsDetail): void
    {
        $this->score = $this->score->withSetsDetail($setsDetail);
        $this->resetConfirmations();
    }

    public function updateSetsToWin(SetsToWin $setsToWin): void
    {
        $this->score = $this->score->withSetsToWin($setsToWin);
        $this->resetConfirmations();
    }

    public function updateFormat(MatchFormat $format): void
    {
        if ($format->isSingles() && $this->composition->participantCount() >= 3) {
            throw CannotSwitchToSinglesWithMultiplePlayersException::create();
        }

        $changed = $this->configuration->format()->value() !== $format->value();
        $this->configuration = $this->configuration->withFormat($format);

        if ($changed) {
            $this->resetConfirmations();
        }
    }

    public function updateType(MatchType $type): void
    {
        $this->configuration = $this->configuration->withType($type);
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

    // --- Composite VO accessors ---

    public function composition(): TeamComposition
    {
        return $this->composition;
    }

    public function configuration(): MatchConfiguration
    {
        return $this->configuration;
    }

    public function matchScore(): MatchScore
    {
        return $this->score;
    }

    public function information(): MatchInformation
    {
        return $this->information;
    }

    public function eloSnapshot(): ?EloSnapshot
    {
        return $this->eloSnapshot;
    }

    // --- Flat delegation accessors (backward-compatible) ---

    public function id(): MatchId
    {
        return $this->id;
    }

    public function status(): MatchStatus
    {
        return $this->status;
    }

    public function createdBy(): PlayerId
    {
        return $this->composition->creator();
    }

    public function type(): MatchType
    {
        return $this->configuration->type();
    }

    public function format(): MatchFormat
    {
        return $this->configuration->format();
    }

    public function courtName(): ?CourtName
    {
        return $this->information->courtName();
    }

    public function notes(): ?Notes
    {
        return $this->information->notes();
    }

    public function matchDate(): ?DateTimeImmutable
    {
        return $this->information->matchDate();
    }

    public function setsDetail(): ?SetsDetail
    {
        return $this->score->setsDetail();
    }

    public function setsToWin(): SetsToWin
    {
        return $this->score->setsToWin();
    }

    public function teamAScore(): ?Score
    {
        return $this->score->teamAScore();
    }

    public function teamBScore(): ?Score
    {
        return $this->score->teamBScore();
    }

    public function teamAEloBefore(): ?EloRating
    {
        return $this->eloSnapshot?->teamABefore();
    }

    public function teamBEloBefore(): ?EloRating
    {
        return $this->eloSnapshot?->teamBBefore();
    }

    public function eloChange(): ?EloChange
    {
        return $this->eloSnapshot?->change();
    }

    public function teamAPlayer1Id(): PlayerId
    {
        return $this->composition->creator();
    }

    public function teamAPlayer2Id(): ?PlayerId
    {
        return $this->composition->partner();
    }

    public function teamBPlayer1Id(): ?PlayerId
    {
        return $this->composition->opponent1();
    }

    public function teamBPlayer2Id(): ?PlayerId
    {
        return $this->composition->opponent2();
    }

    /** @return list<PlayerId> */
    public function confirmedPlayerIds(): array
    {
        return $this->confirmedPlayerIds;
    }
}
