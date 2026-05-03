<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Infrastructure\Persistence\Eloquent\Mappers;

use App\Features\Matches\Infrastructure\Persistence\Eloquent\Mappers\MatchMapper;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchModel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MatchMapperTest extends TestCase
{
    public function test_it_reconstitutes_creator_from_created_by(): void
    {
        $model = new MatchModel([
            'id' => '10000000-0000-0000-0000-000000000001',
            'match_type' => 'friendly',
            'match_format' => 'doubles',
            'status' => 'pending',
            'team_a_player1_id' => '00000000-0000-0000-0000-000000000001',
            'team_a_player2_id' => null,
            'team_b_player1_id' => null,
            'team_b_player2_id' => null,
            'sets_to_win' => 2,
            'created_by' => '00000000-0000-0000-0000-000000000099',
        ]);

        $match = (new MatchMapper)->toDomain($model);

        $this->assertSame('00000000-0000-0000-0000-000000000099', $match->createdBy()->value());
    }
}
