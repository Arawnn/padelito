<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Contracts\MailerInterface;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;
use App\Shared\Infrastructure\Bus\LaravelCommandBus;
use App\Shared\Infrastructure\Bus\LaravelQueryBus;
use App\Shared\Infrastructure\Events\LaravelEventDispatcher;
use App\Shared\Infrastructure\Helpers\UuidGenerator;
use App\Shared\Infrastructure\Services\LaravelMailer;
use Illuminate\Support\ServiceProvider;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);
        $this->app->bind(CommandBusInterface::class, LaravelCommandBus::class);
        $this->app->bind(QueryBusInterface::class, LaravelQueryBus::class);
        $this->app->bind(MailerInterface::class, LaravelMailer::class);
    }
}
