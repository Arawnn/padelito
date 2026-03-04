<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\Entities;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Events\UserLoggedIn;
use App\Features\Auth\Domain\Events\UserLoggedOut;
use App\Features\Auth\Domain\Events\UserPasswordUpdated;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use Tests\Shared\Mother\UserMother;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
   public function test_it_creates_a_user(): void
   {
        $user = UserMother::register();

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

   public function test_it_dispatches_a_user_logged_in_event(): void
   {
        $user = UserMother::reconstitute();

        $user->login();

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserLoggedIn::class, $events->first());
   }

   public function test_it_dispatches_a_user_logged_out_event(): void
   {
        $user = UserMother::reconstitute();

        $user->logout();

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserLoggedOut::class, $events->first());
   }

   public function test_it_updates_a_password(): void
   {
        $user = UserMother::reconstitute();

        $newPassword = HashedPassword::fromHash('new-hash-pour-test');
        $user->updatePassword($newPassword);

        $this->assertSame('new-hash-pour-test', $user->password()->value());
        
        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserPasswordUpdated::class, $events->first());
   }

   public function test_it_clears_domain_events_after_pulling_them(): void
   {
        $user = UserMother::register();

        $first = $user->pullDomainEvents();
        $second = $user->pullDomainEvents();
        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
   }

   public function test_it_does_not_emit_events_when_reconstituted(): void
   {
       $user = UserMother::reconstitute();
       $this->assertCount(0, $user->pullDomainEvents());
   }
}