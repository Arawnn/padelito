<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Query\GetPlayerProfile;

use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQueryHandler;
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

        $this->assertTrue($result->isOk());
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $result->value()->id()->value());
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $result = $this->makeHandler()(new GetPlayerProfileQuery(
            userId: '00000000-0000-0000-0000-000000000099',
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    private function makeHandler(): GetPlayerProfileQueryHandler
    {
        return new GetPlayerProfileQueryHandler(
            playerRepository: $this->repository,
        );
    }
}
