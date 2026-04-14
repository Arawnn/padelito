<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Query\GetPlayerProfile;

use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQueryHandler;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GetPlayerProfileQueryHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
    }

    public function test_it_returns_the_player_when_found(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new GetPlayerProfileQuery(
            userId: '00000000-0000-0000-0000-000000000001',
        ));

        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $result->id()->value());
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new GetPlayerProfileQuery(
            userId: '00000000-0000-0000-0000-000000000099',
        ));
    }

    private function makeHandler(): GetPlayerProfileQueryHandler
    {
        return new GetPlayerProfileQueryHandler(
            playerRepository: $this->repository,
        );
    }
}
