<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Application\Commands\CancelMatch;

use App\Features\Matches\Application\Commands\CancelMatch\CancelMatchCommand;
use App\Features\Matches\Application\Commands\CancelMatch\CancelMatchCommandHandler;
use App\Features\Matches\Domain\Events\MatchCancelled;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use Tests\Shared\Mother\Fake\InMemoryMatchRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\MatchMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CancelMatchCommandHandlerTest extends TestCase
{
    private const CREATOR_ID = '00000000-0000-0000-0000-000000000001';

    private const OTHER_ID = '00000000-0000-0000-0000-000000000099';

    private InMemoryMatchRepository $matchRepository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchRepository = new InMemoryMatchRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_creator_can_cancel_pending_match(): void
    {
        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new CancelMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
        ));

        $updated = $this->matchRepository->findById($match->id());
        $this->assertTrue($updated->status()->isCancelled());
        $this->assertTrue($this->eventDispatcher->dispatched(MatchCancelled::class));
    }

    public function test_non_creator_cannot_cancel(): void
    {
        $this->expectException(UnauthorizedMatchOperationException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new CancelMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::OTHER_ID,
        ));
    }

    public function test_cannot_cancel_validated_match(): void
    {
        $this->expectException(MatchAlreadyValidatedException::class);

        $match = MatchMother::create()->withCreator(self::CREATOR_ID)->withStatus('validated')->build();
        $this->matchRepository->save($match);

        $this->makeHandler()(new CancelMatchCommand(
            matchId: $match->id()->value(),
            requesterId: self::CREATOR_ID,
        ));
    }

    public function test_throws_when_match_not_found(): void
    {
        $this->expectException(MatchNotFoundException::class);

        $this->makeHandler()(new CancelMatchCommand(
            matchId: '99999999-9999-9999-9999-999999999999',
            requesterId: self::CREATOR_ID,
        ));
    }

    private function makeHandler(): CancelMatchCommandHandler
    {
        return new CancelMatchCommandHandler(
            matchRepository: $this->matchRepository,
            eventDispatcher: $this->eventDispatcher,
        );
    }
}
