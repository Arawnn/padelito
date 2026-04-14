<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Query\GetPublicPlayerProfile;

use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQueryHandler;
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
final class GetPublicPlayerProfileQueryHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
    }

    public function test_it_returns_the_player_when_profile_is_public(): void
    {
        $player = PlayerMother::create()
            ->withUsername('jean_dupont')
            ->asPublic()
            ->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new GetPublicPlayerProfileQuery(
            targetUsername: 'jean_dupont',
        ));

        $this->assertInstanceOf(Player::class, $result);
        $this->assertEquals('jean_dupont', $result->username()->value());
    }

    public function test_it_fails_when_profile_is_private(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $player = PlayerMother::create()
            ->withUsername('jean_dupont')
            ->build(); // private by default
        $this->repository->save($player);

        $this->makeHandler()(new GetPublicPlayerProfileQuery(
            targetUsername: 'jean_dupont',
        ));
    }

    public function test_it_fails_when_player_does_not_exist(): void
    {
        $this->expectException(PlayerProfileNotFoundException::class);

        $this->makeHandler()(new GetPublicPlayerProfileQuery(
            targetUsername: 'unknown_user',
        ));
    }

    public function test_private_and_not_found_return_the_same_error_to_avoid_leaking_existence(): void
    {
        $privatePlayer = PlayerMother::create()->withUsername('private_user')->build();
        $this->repository->save($privatePlayer);

        $privateException = null;
        $notFoundException = null;

        try {
            $this->makeHandler()(new GetPublicPlayerProfileQuery(targetUsername: 'private_user'));
        } catch (PlayerProfileNotFoundException $e) {
            $privateException = $e;
        }

        try {
            $this->makeHandler()(new GetPublicPlayerProfileQuery(targetUsername: 'ghost_user'));
        } catch (PlayerProfileNotFoundException $e) {
            $notFoundException = $e;
        }

        $this->assertNotNull($privateException);
        $this->assertNotNull($notFoundException);
        $this->assertEquals(get_class($privateException), get_class($notFoundException));
    }

    private function makeHandler(): GetPublicPlayerProfileQueryHandler
    {
        return new GetPublicPlayerProfileQueryHandler(
            playerRepository: $this->repository,
        );
    }
}
