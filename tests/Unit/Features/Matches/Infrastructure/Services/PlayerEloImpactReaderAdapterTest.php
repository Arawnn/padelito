<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Infrastructure\Services;

use App\Features\Matches\Application\Dto\MatchEloImpactInput;
use App\Features\Matches\Infrastructure\Services\PlayerEloImpactReaderAdapter;
use App\Features\Player\Application\Contracts\MatchEloSummaryReader as PlayerMatchEloSummaryReader;
use App\Features\Player\Application\Dto\MatchEloInput as PlayerMatchEloInput;
use App\Features\Player\Application\Dto\MatchEloSummary as PlayerMatchEloSummary;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PlayerEloImpactReaderAdapterTest extends TestCase
{
    public function test_it_adapts_match_elo_input_and_summary(): void
    {
        $reader = new class implements PlayerMatchEloSummaryReader
        {
            public ?PlayerMatchEloInput $receivedInput = null;

            public function forMatch(PlayerMatchEloInput $input, ?string $currentUserId): ?PlayerMatchEloSummary
            {
                $this->receivedInput = $input;

                return PlayerMatchEloSummary::from(
                    teamABefore: 1510,
                    teamBBefore: 1490,
                    teamAChange: 12,
                    teamBChange: -12,
                    currentUserChange: 12,
                    source: 'projected',
                );
            }

            public function summariesForMatches(array $inputs, ?string $currentUserId): array
            {
                return [];
            }
        };

        $summary = (new PlayerEloImpactReaderAdapter($reader))->forMatch(new MatchEloImpactInput(
            matchId: '10000000-0000-0000-0000-000000000001',
            isRanked: true,
            isValidated: false,
            teamAPlayerIds: ['00000000-0000-0000-0000-000000000001'],
            teamBPlayerIds: ['00000000-0000-0000-0000-000000000002'],
            teamAScore: 2,
            teamBScore: 0,
        ), '00000000-0000-0000-0000-000000000001');

        $this->assertNotNull($reader->receivedInput);
        $this->assertSame('10000000-0000-0000-0000-000000000001', $reader->receivedInput->matchId);
        $this->assertSame(['00000000-0000-0000-0000-000000000001'], $reader->receivedInput->teamAPlayerIds);

        $this->assertNotNull($summary);
        $this->assertSame(1510, $summary->teamABefore);
        $this->assertSame(-12, $summary->teamBChange);
        $this->assertSame('projected', $summary->source);
        $this->assertFalse($summary->toArray()['is_final']);
    }
}
