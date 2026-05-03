<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\CreateMatch;

use App\Features\Matches\Application\Commands\CreateMatch\CreateMatchCommand;
use App\Features\Matches\Application\Commands\CreateMatch\CreateMatchCommandHandler;
use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Events\MatchCreated;
use App\Features\Matches\Domain\Exceptions\PlayerNotRegisteredInAppException;
use Tests\Shared\Mother\Fake\InMemoryMatchRepository;
use Tests\Shared\Mother\Fake\InMemoryPlayerRegistry;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreateMatchCommandHandlerTest extends TestCase
{
    private const CREATOR_ID = '00000000-0000-0000-0000-000000000001';

    private InMemoryMatchRepository $matchRepository;

    private InMemoryPlayerRegistry $playerRegistry;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->playerRegistry = new InMemoryPlayerRegistry;
        $this->eventDispatcher = new SpyEventDispatcher;

        $this->playerRegistry->register(self::CREATOR_ID);
    }

    public function test_it_creates_a_match(): void
    {
        $match = $this->makeHandler()($this->validCommand());

        $this->assertInstanceOf(PadelMatch::class, $match);
        $this->assertNotNull($this->matchRepository->findById($match->id()));
        $this->assertEquals('friendly', $match->type()->value()->value);
        $this->assertEquals('doubles', $match->format()->value()->value);
        $this->assertEquals(self::CREATOR_ID, $match->teamAPlayer1Id()->value());
        $this->assertTrue($match->status()->isPending());
    }

    public function test_it_dispatches_match_created_event(): void
    {
        $this->makeHandler()($this->validCommand());

        $this->assertTrue($this->eventDispatcher->dispatched(MatchCreated::class));
    }

    public function test_it_fails_when_creator_not_registered(): void
    {
        $this->expectException(PlayerNotRegisteredInAppException::class);

        $this->makeHandler()(new CreateMatchCommand(
            creatorId: '99999999-9999-9999-9999-999999999999',
            matchType: 'friendly',
            matchFormat: 'doubles',
        ));
    }

    private function validCommand(): CreateMatchCommand
    {
        return new CreateMatchCommand(
            creatorId: self::CREATOR_ID,
            matchType: 'friendly',
            matchFormat: 'doubles',
            courtName: 'Court 1',
            matchDate: '2026-05-01 10:00:00',
            notes: 'fun match',
        );
    }

    private function makeHandler(): CreateMatchCommandHandler
    {
        return new CreateMatchCommandHandler(
            matchRepository: $this->matchRepository,
            playerRegistry: $this->playerRegistry,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
