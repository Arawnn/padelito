<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Entities;

use App\Features\Matches\Domain\Events\MatchCreated;
use App\Features\Matches\Domain\States\MatchStateFactory;
use App\Features\Matches\Domain\States\MatchStateInterface;
use App\Features\Matches\Domain\States\MatchStatusPendingState;
use App\Features\Matches\Domain\ValueObjects\CourtName;
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
use App\Shared\Domain\Events\DomainEvent;
use DateTimeImmutable;

final class PadelMatch extends AggregateRoot
{
    /** @param list<PlayerId> $confirmedPlayerIds */
    private function __construct(
        private readonly MatchId $id,
        private MatchStateInterface $state,
        private TeamComposition $composition,
        private MatchConfiguration $configuration,
        private MatchScore $score,
        private MatchInformation $information,
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
            state: new MatchStatusPendingState,
            composition: TeamComposition::withCreator($createdBy),
            configuration: MatchConfiguration::from($type, $format),
            score: MatchScore::empty($setsToWin ?? SetsToWin::fromInt(2)),
            information: MatchInformation::reconstitute($courtName, $notes, $matchDate),
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
        array $confirmedPlayerIds,
    ): self {
        return new self(
            id: $id,
            state: MatchStateFactory::fromStatus($status),
            composition: TeamComposition::reconstitute($creator, $partner, $opponent1, $opponent2),
            configuration: MatchConfiguration::from($type, $format),
            score: MatchScore::reconstitute($teamAScore, $teamBScore, $setsDetail, $setsToWin),
            information: MatchInformation::reconstitute($courtName, $notes, $matchDate),
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
            if ($this->configuration->format()->isSingles()) {
                return true;
            }

            return $this->composition->partner() !== null;
        }

        if ($this->configuration->format()->isSingles()) {
            return $this->composition->opponent1() !== null;
        }

        return $this->composition->opponent1() !== null && $this->composition->opponent2() !== null;
    }

    public function canAcceptInvitation(PlayerId $playerId, Team $team): bool
    {
        return $this->state->canAcceptInvitation($this, $playerId, $team);
    }

    public function ensureCanInvitePlayer(PlayerId $playerId, Team $team): void
    {
        $this->state->ensureCanInvitePlayer($this, $playerId, $team);
    }

    public function ensureCanRespondToInvitation(): void
    {
        $this->state->ensureCanRespondToInvitation();
    }

    public function ensureCanUpdate(): void
    {
        $this->state->ensureCanUpdate();
    }

    public function assignPlayer(PlayerId $playerId, Team $team): void
    {
        $this->state->assignPlayer($this, $playerId, $team);
    }

    public function removePlayer(PlayerId $playerId): void
    {
        $this->state->removePlayer($this, $playerId);
    }

    public function confirm(PlayerId $playerId): void
    {
        $this->state->confirm($this, $playerId);
    }

    public function updateCourtName(?CourtName $courtName): void
    {
        $this->state->updateCourtName($this, $courtName);
    }

    public function updateMatchDate(?DateTimeImmutable $matchDate): void
    {
        $this->state->updateMatchDate($this, $matchDate);
    }

    public function updateNotes(?Notes $notes): void
    {
        $this->state->updateNotes($this, $notes);
    }

    public function updateSetsDetail(?SetsDetail $setsDetail): void
    {
        $this->state->updateSetsDetail($this, $setsDetail);
    }

    public function updateSetsToWin(SetsToWin $setsToWin): void
    {
        $this->state->updateSetsToWin($this, $setsToWin);
    }

    public function updateFormat(MatchFormat $format): void
    {
        $this->state->updateFormat($this, $format);
    }

    public function updateType(MatchType $type): void
    {
        $this->state->updateType($this, $type);
    }

    public function cancel(): void
    {
        $this->state->cancel($this);
    }

    public function replaceComposition(TeamComposition $composition): void
    {
        $this->composition = $composition;
    }

    public function replaceConfiguration(MatchConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function replaceScore(MatchScore $score): void
    {
        $this->score = $score;
    }

    public function replaceInformation(MatchInformation $information): void
    {
        $this->information = $information;
    }

    /** @param list<PlayerId> $confirmedPlayerIds */
    public function replaceConfirmedPlayerIds(array $confirmedPlayerIds): void
    {
        $this->confirmedPlayerIds = $confirmedPlayerIds;
    }

    public function addConfirmedPlayerId(PlayerId $playerId): void
    {
        $this->confirmedPlayerIds[] = $playerId;
    }

    public function transitionTo(MatchStateInterface $state): void
    {
        $this->state = $state;
    }

    public function recordMatchEvent(DomainEvent $event): void
    {
        $this->recordDomainEvent($event);
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

    // --- Flat delegation accessors (backward-compatible) ---

    public function id(): MatchId
    {
        return $this->id;
    }

    public function status(): MatchStatus
    {
        return $this->state->status();
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
