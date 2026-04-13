<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Query\GetPublicPlayerProfile;

use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQueryHandler;
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

        $this->assertTrue($result->isOk());
        $this->assertEquals('jean_dupont', $result->value()->username()->value());
    }

    public function test_it_fails_when_profile_is_private(): void
    {
        $player = PlayerMother::create()
            ->withUsername('jean_dupont')
            ->build(); // private by default
        $this->repository->save($player);

        $result = $this->makeHandler()(new GetPublicPlayerProfileQuery(
            targetUsername: 'jean_dupont',
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    public function test_it_fails_when_player_does_not_exist(): void
    {
        $result = $this->makeHandler()(new GetPublicPlayerProfileQuery(
            targetUsername: 'unknown_user',
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    public function test_private_and_not_found_return_the_same_error_to_avoid_leaking_existence(): void
    {
        // Private profile
        $privatePlayer = PlayerMother::create()->withUsername('private_user')->build();
        $this->repository->save($privatePlayer);

        $privateResult = $this->makeHandler()(new GetPublicPlayerProfileQuery(targetUsername: 'private_user'));
        $notFoundResult = $this->makeHandler()(new GetPublicPlayerProfileQuery(targetUsername: 'ghost_user'));

        $this->assertEquals(
            get_class($privateResult->error()),
            get_class($notFoundResult->error()),
        );
    }

    private function makeHandler(): GetPublicPlayerProfileQueryHandler
    {
        return new GetPublicPlayerProfileQueryHandler(
            playerRepository: $this->repository,
        );
    }
}
