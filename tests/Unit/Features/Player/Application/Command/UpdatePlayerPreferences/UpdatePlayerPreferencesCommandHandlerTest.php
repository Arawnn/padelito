<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\UpdatePlayerPreferences;

use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommand;
use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommandHandler;
use App\Features\Player\Domain\Events\PlayerPreferencesUpdated;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Shared\Application\Optional;
use Tests\Shared\Mother\Fake\ImmediateTransactionManager;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UpdatePlayerPreferencesCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_updates_dominant_hand(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            dominantHand: Optional::of('left'),
            preferredPosition: Optional::absent(),
            location: Optional::absent(),
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('left', $result->value()->preferences()->dominantHand()->value()->value);
    }

    public function test_it_keeps_existing_dominant_hand_when_absent(): void
    {
        // PlayerMother builds with dominantHand = 'right'
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            dominantHand: Optional::absent(),
            preferredPosition: Optional::absent(),
            location: Optional::absent(),
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('right', $result->value()->preferences()->dominantHand()->value()->value);
    }

    public function test_it_clears_dominant_hand_when_explicitly_null(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            dominantHand: Optional::of(null),
            preferredPosition: Optional::absent(),
            location: Optional::absent(),
        ));

        $this->assertTrue($result->isOk());
        $this->assertNull($result->value()->preferences()->dominantHand());
    }

    public function test_it_updates_location(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            dominantHand: Optional::absent(),
            preferredPosition: Optional::absent(),
            location: Optional::of('Paris'),
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('Paris', $result->value()->preferences()->location()->value());
    }

    public function test_it_dispatches_player_preferences_updated_event(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();
        $this->repository->save($player);

        $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            dominantHand: Optional::of('left'),
            preferredPosition: Optional::absent(),
            location: Optional::absent(),
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerPreferencesUpdated::class));
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $result = $this->makeHandler()(new UpdatePlayerPreferencesCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            dominantHand: Optional::absent(),
            preferredPosition: Optional::absent(),
            location: Optional::absent(),
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    private function makeHandler(): UpdatePlayerPreferencesCommandHandler
    {
        return new UpdatePlayerPreferencesCommandHandler(
            playerRepository: $this->repository,
            eventDispatcher: $this->eventDispatcher,
            transactionManager: new ImmediateTransactionManager,
        );
    }
}
