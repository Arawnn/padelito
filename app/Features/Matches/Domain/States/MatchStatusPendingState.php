<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\States;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Events\MatchCancelled;
use App\Features\Matches\Domain\Events\MatchConfirmationsReset;
use App\Features\Matches\Domain\Events\MatchPlayerConfirmed;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Exceptions\CannotSwitchToSinglesWithMultiplePlayersException;
use App\Features\Matches\Domain\Exceptions\DuplicatePlayerInMatchException;
use App\Features\Matches\Domain\Exceptions\MatchNotReadyForConfirmationException;
use App\Features\Matches\Domain\Exceptions\MatchTeamFullException;
use App\Features\Matches\Domain\Exceptions\PlayerAlreadyConfirmedException;
use App\Features\Matches\Domain\Exceptions\PlayerNotParticipantException;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use DateTimeImmutable;

final readonly class MatchStatusPendingState implements MatchStateInterface
{
    public function status(): MatchStatus
    {
        return MatchStatus::pending();
    }

    public function canAcceptInvitation(PadelMatch $match, PlayerId $playerId, Team $team): bool
    {
        return ! $match->isParticipant($playerId) && ! $match->isTeamFull($team);
    }

    public function ensureCanInvitePlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        if ($match->isParticipant($playerId)) {
            throw DuplicatePlayerInMatchException::create();
        }

        if ($match->isTeamFull($team)) {
            throw MatchTeamFullException::create();
        }
    }

    public function ensureCanRespondToInvitation(): void {}

    public function ensureCanUpdate(): void {}

    public function assignPlayer(PadelMatch $match, PlayerId $playerId, Team $team): void
    {
        $this->ensureCanInvitePlayer($match, $playerId, $team);

        if ($team->isA()) {
            $match->replaceComposition($match->composition()->withPartner($playerId));
        } elseif ($match->composition()->opponent1() === null) {
            $match->replaceComposition($match->composition()->withOpponent1($playerId));
        } else {
            $match->replaceComposition($match->composition()->withOpponent2($playerId));
        }

        $this->resetConfirmations($match);
    }

    public function removePlayer(PadelMatch $match, PlayerId $playerId): void
    {
        if ($match->composition()->partner()?->equals($playerId) === true) {
            $match->replaceComposition($match->composition()->withoutPartner());
            $this->resetConfirmations($match);

            return;
        }

        if ($match->composition()->opponent1()?->equals($playerId) === true) {
            $newComposition = $match->composition()->withoutOpponent1();
            if ($newComposition->opponent2() !== null) {
                $newComposition = $newComposition
                    ->withOpponent1($newComposition->opponent2())
                    ->withoutOpponent2();
            }

            $match->replaceComposition($newComposition);
            $this->resetConfirmations($match);

            return;
        }

        if ($match->composition()->opponent2()?->equals($playerId) === true) {
            $match->replaceComposition($match->composition()->withoutOpponent2());
            $this->resetConfirmations($match);
        }
    }

    public function confirm(PadelMatch $match, PlayerId $playerId): void
    {
        if (! $match->isReadyForConfirmation()) {
            throw MatchNotReadyForConfirmationException::create();
        }

        if (! $match->isParticipant($playerId)) {
            throw PlayerNotParticipantException::create();
        }

        foreach ($match->confirmedPlayerIds() as $confirmed) {
            if ($confirmed->equals($playerId)) {
                throw PlayerAlreadyConfirmedException::create();
            }
        }

        $match->addConfirmedPlayerId($playerId);
        $match->recordMatchEvent(new MatchPlayerConfirmed($match->id()->value(), $playerId->value()));

        if ($match->hasAllParticipantsConfirmed()) {
            $this->finalize($match);
        }
    }

    public function updateCourtName(PadelMatch $match, ?CourtName $courtName): void
    {
        $match->replaceInformation($match->information()->withCourtName($courtName));
    }

    public function updateMatchDate(PadelMatch $match, ?DateTimeImmutable $matchDate): void
    {
        $match->replaceInformation($match->information()->withMatchDate($matchDate));
    }

    public function updateNotes(PadelMatch $match, ?Notes $notes): void
    {
        $match->replaceInformation($match->information()->withNotes($notes));
    }

    public function updateSetsDetail(PadelMatch $match, ?SetsDetail $setsDetail): void
    {
        $match->replaceScore($match->matchScore()->withSetsDetail($setsDetail));
        $this->resetConfirmations($match);
    }

    public function updateSetsToWin(PadelMatch $match, SetsToWin $setsToWin): void
    {
        $match->replaceScore($match->matchScore()->withSetsToWin($setsToWin));
        $this->resetConfirmations($match);
    }

    public function updateFormat(PadelMatch $match, MatchFormat $format): void
    {
        if ($format->isSingles() && ($match->composition()->partner() !== null || $match->composition()->opponent2() !== null)) {
            throw CannotSwitchToSinglesWithMultiplePlayersException::create();
        }

        $changed = $match->configuration()->format()->value() !== $format->value();
        $match->replaceConfiguration($match->configuration()->withFormat($format));

        if ($changed) {
            $this->resetConfirmations($match);
        }
    }

    public function updateType(PadelMatch $match, MatchType $type): void
    {
        $match->replaceConfiguration($match->configuration()->withType($type));
        $this->resetConfirmations($match);
    }

    public function cancel(PadelMatch $match): void
    {
        $match->transitionTo(new MatchStatusCancelledState);
        $match->recordMatchEvent(new MatchCancelled($match->id()->value()));
    }

    private function finalize(PadelMatch $match): void
    {
        $match->replaceScore($match->matchScore()->withFinalizedScores());
        $match->transitionTo(new MatchStatusValidatedState);
        [$teamAScore, $teamBScore] = $match->derivedScores();

        $match->recordMatchEvent(new MatchValidated(
            matchId: $match->id()->value(),
            teamAPlayerIds: $this->playerIdValues([$match->teamAPlayer1Id(), $match->teamAPlayer2Id()]),
            teamBPlayerIds: $this->playerIdValues([$match->teamBPlayer1Id(), $match->teamBPlayer2Id()]),
            teamAScore: $teamAScore,
            teamBScore: $teamBScore,
            ranked: $match->type()->isRanked(),
        ));
    }

    private function resetConfirmations(PadelMatch $match): void
    {
        if ($match->confirmedPlayerIds() !== []) {
            $match->replaceConfirmedPlayerIds([]);
            $match->recordMatchEvent(new MatchConfirmationsReset($match->id()->value()));
        }
    }

    /**
     * @param  list<PlayerId|null>  $playerIds
     * @return list<string>
     */
    private function playerIdValues(array $playerIds): array
    {
        return array_values(array_map(
            fn (PlayerId $playerId): string => $playerId->value(),
            array_filter($playerIds),
        ));
    }
}
