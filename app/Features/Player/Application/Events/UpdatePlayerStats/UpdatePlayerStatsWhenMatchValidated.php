<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Events\UpdatePlayerStats;

use App\Features\Matches\Domain\Events\MatchValidated;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\Services\EloCalculationService;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEventSubscriberInterface;

final readonly class UpdatePlayerStatsWhenMatchValidated implements DomainEventSubscriberInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private EloCalculationService $eloCalculationService,
        private EloHistoryRepositoryInterface $eloHistoryRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public static function subscribedTo(): array
    {
        return [MatchValidated::class];
    }

    public function __invoke(MatchValidated $event): void
    {
        $players = $this->loadPlayers($event);

        [$teamAChange, $teamBChange] = $event->ranked
            ? $this->computeEloChanges($event, $players)
            : [0, 0];

        $teamAWon = $event->teamAScore > $event->teamBScore;
        $teamBWon = $event->teamBScore > $event->teamAScore;

        $this->applyResultsToTeam($event, $players, $event->teamAPlayerIds, $teamAWon, $teamAChange);
        $this->applyResultsToTeam($event, $players, $event->teamBPlayerIds, $teamBWon, $teamBChange);
    }

    /** @return array<string, Player> */
    private function loadPlayers(MatchValidated $event): array
    {
        $players = [];

        foreach ([...$event->teamAPlayerIds, ...$event->teamBPlayerIds] as $playerId) {
            $player = $this->playerRepository->findById(Id::fromString($playerId));
            if ($player === null) {
                throw PlayerProfileNotFoundException::create();
            }

            $players[$playerId] = $player;
        }

        return $players;
    }

    /** @param array<string, Player> $players */
    private function computeEloChanges(MatchValidated $event, array $players): array
    {
        $teamAElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $event->teamAPlayerIds);
        $teamBElos = array_map(fn (string $id): int => $players[$id]->stats()->eloRating()->value(), $event->teamBPlayerIds);
        $teamAMatchCounts = array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $event->teamAPlayerIds);
        $teamBMatchCounts = array_map(fn (string $id): int => $players[$id]->stats()->totalMatches(), $event->teamBPlayerIds);

        $result = $this->eloCalculationService->calculate(
            teamAElos: $teamAElos,
            teamBElos: $teamBElos,
            teamAMatchCounts: $teamAMatchCounts,
            teamBMatchCounts: $teamBMatchCounts,
            teamAScore: $event->teamAScore,
            teamBScore: $event->teamBScore,
        );

        return [$result->teamAChange, $result->teamBChange];
    }

    /**
     * @param  array<string, Player>  $players
     * @param  list<string>  $teamPlayerIds
     */
    private function applyResultsToTeam(MatchValidated $event, array $players, array $teamPlayerIds, bool $won, int $eloChange): void
    {
        foreach ($teamPlayerIds as $playerId) {
            $player = $players[$playerId];
            $eloBefore = $player->stats()->eloRating()->value();
            $change = $event->ranked ? $eloChange : 0;
            $newStats = $player->stats()->withMatchResult($won, $change);

            $player->applyMatchResult($newStats, $event->matchId, $won, $change);
            $playerEvents = $player->pullDomainEvents();

            $this->playerRepository->save($player);

            if ($event->ranked) {
                $this->eloHistoryRepository->record(
                    playerId: $playerId,
                    matchId: $event->matchId,
                    eloBefore: $eloBefore,
                    eloAfter: $player->stats()->eloRating()->value(),
                    eloChange: $change,
                );
            }

            $this->eventDispatcher->dispatchEvents($playerEvents);
        }
    }
}
