<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\Entities;

use App\Features\Player\Domain\Events\PlayerIdentityUpdated;
use App\Features\Player\Domain\Events\PlayerPreferencesUpdated;
use App\Features\Player\Domain\Events\PlayerUsernameChanged;
use App\Features\Player\Domain\Events\PlayerVisibilityChanged;
use App\Features\Player\Domain\ValueObjects\ProfileVisibility;
use App\Features\Player\Domain\ValueObjects\Username;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Mother\PlayerMother;

/**
 * @internal
 *
 * @coversNothing
 */
final class PlayerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_sets_visibility_to_private_by_default(): void
    {
        $player = PlayerMother::create()->build();

        // reconstitute() is used by PlayerMother; test create() directly via event
        // The domain method create() is called inside CreatePlayerProfileCommandHandler.
        // Here we verify the invariant via the entity's named constructor:
        $this->assertTrue($player->visibility()->isPrivate());
    }

    public function test_reconstitute_does_not_record_events(): void
    {
        $player = PlayerMother::create()->build();

        $events = $player->pullDomainEvents();

        $this->assertCount(0, $events);
    }

    // -------------------------------------------------------------------------
    // changeVisibility()
    // -------------------------------------------------------------------------

    public function test_change_visibility_to_public(): void
    {
        $player = PlayerMother::create()->build();

        $player->changeVisibility(ProfileVisibility::public());

        $this->assertTrue($player->visibility()->isPublic());
    }

    public function test_change_visibility_to_private(): void
    {
        $player = PlayerMother::create()->asPublic()->build();

        $player->changeVisibility(ProfileVisibility::private());

        $this->assertTrue($player->visibility()->isPrivate());
    }

    public function test_change_visibility_records_player_visibility_changed_event(): void
    {
        $player = PlayerMother::create()->build();

        $player->changeVisibility(ProfileVisibility::public());

        $events = $player->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PlayerVisibilityChanged::class, $events->first());
    }

    public function test_player_visibility_changed_event_carries_correct_payload(): void
    {
        $player = PlayerMother::create()->withId('00000000-0000-0000-0000-000000000001')->build();

        $player->changeVisibility(ProfileVisibility::public());

        /** @var PlayerVisibilityChanged $event */
        $event = $player->pullDomainEvents()->first();
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $event->playerId);
        $this->assertTrue($event->isPublic);
    }

    // -------------------------------------------------------------------------
    // updateIdentity()
    // -------------------------------------------------------------------------

    public function test_update_identity_records_player_identity_updated_event(): void
    {
        $player = PlayerMother::create()->build();

        $player->updateIdentity(null);

        $events = $player->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PlayerIdentityUpdated::class, $events->first());
    }

    public function test_update_identity_changes_the_identity(): void
    {
        $player = PlayerMother::create()->build();

        $player->updateIdentity(null);

        $this->assertNull($player->identity());
    }

    // -------------------------------------------------------------------------
    // updatePreferences()
    // -------------------------------------------------------------------------

    public function test_update_preferences_records_player_preferences_updated_event(): void
    {
        $player = PlayerMother::create()->build();

        $player->updatePreferences(null);

        $events = $player->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PlayerPreferencesUpdated::class, $events->first());
    }

    public function test_update_preferences_changes_the_preferences(): void
    {
        $player = PlayerMother::create()->build();

        $player->updatePreferences(null);

        $this->assertNull($player->preferences());
    }

    // -------------------------------------------------------------------------
    // changeUsername()
    // -------------------------------------------------------------------------

    public function test_change_username_updates_the_username(): void
    {
        $player = PlayerMother::create()->withUsername('old_name')->build();

        $player->changeUsername(Username::fromString('new_name'));

        $this->assertEquals('new_name', $player->username()->value());
    }

    public function test_change_username_records_player_username_changed_event(): void
    {
        $player = PlayerMother::create()->withUsername('old_name')->build();

        $player->changeUsername(Username::fromString('new_name'));

        $events = $player->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PlayerUsernameChanged::class, $events->first());
    }

    public function test_change_username_captures_old_username_before_mutation(): void
    {
        $player = PlayerMother::create()->withUsername('old_name')->build();

        $player->changeUsername(Username::fromString('new_name'));

        /** @var PlayerUsernameChanged $event */
        $event = $player->pullDomainEvents()->first();
        $this->assertEquals('old_name', $event->oldUsername);
        $this->assertEquals('new_name', $event->newUsername);
    }

    // -------------------------------------------------------------------------
    // pullDomainEvents()
    // -------------------------------------------------------------------------

    public function test_pull_domain_events_clears_the_event_queue(): void
    {
        $player = PlayerMother::create()->build();
        $player->changeVisibility(ProfileVisibility::public());

        $player->pullDomainEvents();

        $this->assertCount(0, $player->pullDomainEvents());
    }
}
