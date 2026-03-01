<?php

namespace App\Features\Auth\Infrastructure\Providers;

use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommand;
use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommandHandler;
use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommand;
use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommandHandler;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommandHandler;
use App\Features\Auth\Application\EventHandlers\CreateUserProfileOnUserCreated;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQueryHandler;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Repositories\EloquentUserRepository;
use App\Features\Auth\Infrastructure\Security\LaravelPasswordHasher;
use App\Features\Auth\Infrastructure\Security\SanctumTokenCreator;
use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Infrastructure\Transaction\LaravalTransactionManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenCreatorInterface::class, SanctumTokenCreator::class);
        $this->app->bind(TransactionManagerInterface::class, LaravalTransactionManager::class);
        $this->app->singleton(HandlerMap::class, function() {
            $map = new HandlerMap();
            $map->register(RegisterUserCommand::class, RegisterUserCommandHandler::class);
            $map->register(LoginUserCommand::class, LoginUserCommandHandler::class);
            $map->register(LogoutUserCommand::class, LogoutUserCommandHandler::class);
            $map->register(UpdateUserPasswordCommand::class, UpdateUserPasswordCommandHandler::class);
            $map->register(GetUserByEmailQuery::class, GetUserByEmailQueryHandler::class);

            return $map;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }


    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

        // RateLimiter::for('login', function (Request $request) {
        //     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

        //     return Limit::perMinute(5)->by($throttleKey);
        // });
    }
}
