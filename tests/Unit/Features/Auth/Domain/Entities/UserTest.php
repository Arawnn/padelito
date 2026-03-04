<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Auth\Domain\Entities;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Password;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
   public function test_it_creates_a_user(): void
   {
        $id = Id::fromString('id-fixe-test');
        $name = Name::fromString('John Doe');
        $email = Email::fromString('john.doe@example.com');
        $password = HashedPassword::fromHash('hash-fake-pour-test');

        $user = User::register(
            id: $id,
            name: $name,
            email: $email,
            password: $password
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($id, $user->id());
        $this->assertEquals('John Doe', $user->name()->value());
        $this->assertEquals('john.doe@example.com', $user->email()->value());
        $this->assertSame($password, $user->password());

        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserCreated::class, $events->first());
   }
}