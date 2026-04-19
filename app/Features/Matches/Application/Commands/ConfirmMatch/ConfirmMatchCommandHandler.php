<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\ConfirmMatch;

use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\Services\EloCalculationService;
use App\Features\Matches\Domain\ValueObjects\EloChange;
use App\Features\Matches\Domain\ValueObjects\EloRating;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class ConfirmMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private PlayerRepositoryInterface $playerRepository,
        private EloCalculationService $eloCalculationService,
        private EloHistoryRepositoryInterface $eloHistoryRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ConfirmMatchCommand $command): void
    {
        $match = $this->matchRepository->findByIdWithLock(MatchId::fromString($command->matchId));
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        $match->confirm(PlayerId::fromString($command->playerId));

        $domainEvents = $match->pullDomainEvents();

        $isValidated = false;
        foreach ($domainEvents as $event) {
            if ($event instanceof MatchValidated) {
                $isValidated = true;
                break;
            }
        }

        if ($isValidated) {
            $this->finalizeMatch($match);
        }

        $this->matchRepository->save($match);

        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($domainEvents));
    }

    private function finalizeMatch(PadelMatch $match): void
    {
        $players = $this->loadPlayers($match);

        $teamAIds = array_values(array_filter([$match->teamAPlayer1Id(), $match->teamAPlayer2Id()]));
        $teamBIds = array_values(array_filter([$match->teamBPlayer1Id(), $match->teamBPlayer2Id()]));

        $isRanked = $match->type()->isRanked();
        [$teamAScore, $teamBScore] = $match->derivedScores();

        [$eloChangeA, $eloChangeB] = $isRanked
            ? $this->computeEloChanges($match, $players, $teamAIds, $teamBIds, $teamAScore, $teamBScore)
            : [0, 0];

        $winningTeam = $match->winningTeam();

        $this->applyResultsToTeam($match, $players, $teamAIds, $winningTeam !== null && $winningTeam->isA(), $eloChangeA, $isRanked);
        $this->applyResultsToTeam($match, $players, $teamBIds, $winningTeam !== null && ! $winningTeam->isA(), $eloChangeB, $isRanked);
    }

    /** @return array<string, Player> */
    private function loadPlayers(PadelMatch $match): array
    {
        $players = [];
        foreach ($match->participantIds() as $pid) {
            $player = $this->playerRepository->findById(Id::fromString($pid->value()));
            if ($player !== null) {
                $players[$pid->value()] = $player;
            }
        }

        return $players;
    }

    /** @param list<PlayerId> $teamAIds @param list<PlayerId> $teamBIds @param array<string, Player> $players */
    private function computeEloChanges(PadelMatch $match, array $players, array $teamAIds, array $teamBIds, int $teamAScore, int $teamBScore): array
    {
        $teamAElos = array_map(fn (PlayerId $id) => $players[$id->value()]->stats()->eloRating()->value(), $teamAIds);
        $teamBElos = array_map(fn (PlayerId $id) => $players[$id->value()]->stats()->eloRating()->value(), $teamBIds);
        $teamAMatchCounts = array_map(fn (PlayerId $id) => $players[$id->value()]->stats()->totalMatches(), $teamAIds);
        $teamBMatchCounts = array_map(fn (PlayerId $id) => $players[$id->value()]->stats()->totalMatches(), $teamBIds);

        $result = $this->eloCalculationService->calculate(
            teamAElos: $teamAElos,
            teamBElos: $teamBElos,
            teamAMatchCounts: $teamAMatchCounts,
            teamBMatchCounts: $teamBMatchCounts,
            teamAScore: $teamAScore,
            teamBScore: $teamBScore,
        );

        $teamAAvgElo = (int) round(array_sum($teamAElos) / \count($teamAElos));
        $teamBAvgElo = (int) round(array_sum($teamBElos) / \count($teamBElos));
        $match->recordEloSnapshot(EloRating::fromInt($teamAAvgElo), EloRating::fromInt($teamBAvgElo), EloChange::fromInt($result->teamAChange));

        return [$result->teamAChange, $result->teamBChange];
    }

    /** @param list<PlayerId> $teamIds @param array<string, Player> $players */
    private function applyResultsToTeam(PadelMatch $match, array $players, array $teamIds, bool $won, int $eloChange, bool $isRanked): void
    {
        foreach ($teamIds as $pid) {
            $player = $players[$pid->value()] ?? null;
            if ($player === null) {
                continue;
            }

            $eloBefore = $player->stats()->eloRating()->value();
            $change = $isRanked ? $eloChange : 0;
            $newStats = $player->stats()->withMatchResult($won, $change);
            $player->applyMatchResult($newStats, $match->id()->value(), $won, $change);
            $this->playerRepository->save($player);

            if ($isRanked) {
                $this->eloHistoryRepository->record(
                    playerId: $pid->value(),
                    matchId: $match->id()->value(),
                    eloBefore: $eloBefore,
                    eloAfter: $player->stats()->eloRating()->value(),
                    eloChange: $change,
                );
            }
        }
    }
}
