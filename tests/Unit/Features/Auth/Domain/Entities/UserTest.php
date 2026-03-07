<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\Entities;

use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Events\UserLoggedIn;
use App\Features\Auth\Domain\Events\UserLoggedOut;
use App\Features\Auth\Domain\Events\UserPasswordUpdated;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Mother\UserMother;

final class UserTest extends TestCase
{
    public function testItCreatesAUser(): void
    {
        $user = UserMother::create()->registered()->build();

        $this->assertSame('id-fixe-test', $user->id()->value());
        $this->assertSame('John Doe', $user->name()->value());
        $this->assertSame('john.doe@example.com', $user->email()->value());
        $this->assertSame('hash-fake-pour-test', $user->password()->value());

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertInstanceOf(UserCreated::class, $event);
        $this->assertSame($user->id()->value(), $event->aggregateId);
    }

    public function testItDispatchesAUserLoggedInEvent(): void
    {
        $user = UserMother::create()->build();

        $user->login();

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserLoggedIn::class, $events->first());
    }

    public function testItDispatchesAUserLoggedOutEvent(): void
    {
        $user = UserMother::create()->build();

        $user->logout();

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserLoggedOut::class, $events->first());
    }

    public function testItUpdatesAPassword(): void
    {
        $user = UserMother::create()->build();

        $newPassword = HashedPassword::fromHash('new-hash-pour-test');
        $user->updatePassword($newPassword);

        $this->assertSame('new-hash-pour-test', $user->password()->value());

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserPasswordUpdated::class, $events->first());
    }

    public function testItClearsDomainEventsAfterPullingThem(): void
    {
        $user = UserMother::create()->registered()->build();

        $first = $user->pullDomainEvents();
        $second = $user->pullDomainEvents();
        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
    }

    public function testItDoesNotEmitEventsWhenReconstituted(): void
    {
        $user = UserMother::create()->build();
        $this->assertCount(0, $user->pullDomainEvents());
    }
}
