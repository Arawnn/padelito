<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Contracts\FileStorageInterface;
use App\Shared\Domain\Contracts\ImageFetchInterface;
use App\Shared\Domain\Contracts\MailerInterface;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;
use App\Shared\Infrastructure\Bus\LaravelCommandBus;
use App\Shared\Infrastructure\Bus\LaravelQueryBus;
use App\Shared\Infrastructure\Events\DomainEventSubscriberDispatcher;
use App\Shared\Infrastructure\Helpers\UuidGenerator;
use App\Shared\Infrastructure\Http\SafeHttpImageFetcher;
use App\Shared\Infrastructure\Services\LaravelMailer;
use App\Shared\Infrastructure\Storage\S3FileStorage;
use App\Shared\Infrastructure\Transaction\LaravalTransactionManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(TransactionManagerInterface::class, LaravalTransactionManager::class);
        $this->app->bind(EventDispatcherInterface::class, function ($app): DomainEventSubscriberDispatcher {
            return new DomainEventSubscriberDispatcher($app->tagged('domain_event_subscribers'));
        });
        $this->app->bind(CommandBusInterface::class, LaravelCommandBus::class);
        $this->app->bind(QueryBusInterface::class, LaravelQueryBus::class);
        $this->app->bind(MailerInterface::class, LaravelMailer::class);
        // TODO: Better way to do this
        $this->app->bind(FileStorageInterface::class, function ($app) {
            $config = $app['config']->get('filesystems.disks.s3');
            $useS3 = is_array($config)
                && ! empty($config['bucket'])
                && ! empty($config['region']);

            $diskName = $useS3 ? 's3' : 'public';

            return new S3FileStorage(Storage::disk($diskName));
        });
        $this->app->bind(ImageFetchInterface::class, SafeHttpImageFetcher::class);
    }
}
