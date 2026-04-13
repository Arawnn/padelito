<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Application\Command\ChangeUsername;

use App\Features\Player\Application\Commands\ChangeUsername\ChangeUsernameCommand;
use App\Features\Player\Application\Commands\ChangeUsername\ChangeUsernameCommandHandler;
use App\Features\Player\Domain\Events\PlayerUsernameChanged;
use App\Features\Player\Domain\Exceptions\PlayerProfileNotFoundException;
use App\Features\Player\Domain\Exceptions\UsernameAlreadyTakenException;
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
final class ChangeUsernameCommandHandlerTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_changes_the_username(): void
    {
        $player = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('old_name')
            ->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            newUsername: 'new_name',
        ));

        $this->assertTrue($result->isOk());
        $this->assertEquals('new_name', $result->value()->username()->value());
    }

    public function test_it_is_a_no_op_when_username_is_the_same(): void
    {
        $player = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('jean_dupont')
            ->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            newUsername: 'jean_dupont',
        ));

        $this->assertTrue($result->isOk());
        $this->assertFalse($this->eventDispatcher->dispatched(PlayerUsernameChanged::class));
    }

    public function test_it_dispatches_player_username_changed_event(): void
    {
        $player = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('old_name')
            ->build();
        $this->repository->save($player);

        $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            newUsername: 'new_name',
        ));

        $this->assertTrue($this->eventDispatcher->dispatched(PlayerUsernameChanged::class));
    }

    public function test_it_dispatches_event_with_old_and_new_username(): void
    {
        $player = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('old_name')
            ->build();
        $this->repository->save($player);

        $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            newUsername: 'new_name',
        ));

        /** @var PlayerUsernameChanged $event */
        $event = $this->eventDispatcher->first(PlayerUsernameChanged::class);
        $this->assertEquals('old_name', $event->oldUsername);
        $this->assertEquals('new_name', $event->newUsername);
    }

    public function test_it_fails_when_username_is_already_taken(): void
    {
        $existing = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000099')
            ->withUsername('taken_name')
            ->build();
        $this->repository->save($existing);

        $player = PlayerMother::create()
            ->withId('00000000-0000-0000-0000-000000000001')
            ->withUsername('old_name')
            ->build();
        $this->repository->save($player);

        $result = $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000001',
            newUsername: 'taken_name',
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(UsernameAlreadyTakenException::class, $result->error());
    }

    public function test_it_fails_when_player_not_found(): void
    {
        $result = $this->makeHandler()(new ChangeUsernameCommand(
            userId: '00000000-0000-0000-0000-000000000099',
            newUsername: 'new_name',
        ));

        $this->assertTrue($result->isFail());
        $this->assertInstanceOf(PlayerProfileNotFoundException::class, $result->error());
    }

    private function makeHandler(): ChangeUsernameCommandHandler
    {
        return new ChangeUsernameCommandHandler(
            playerRepository: $this->repository,
            eventDispatcher: $this->eventDispatcher,
            transactionManager: new ImmediateTransactionManager,
        );
    }
}
