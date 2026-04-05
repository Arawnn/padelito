<?php

namespace App\Features\Auth\Infrastructure\Providers;

use App\Features\Auth\Application\Commands\ConfirmPasswordReset\ConfirmPasswordResetCommand;
use App\Features\Auth\Application\Commands\ConfirmPasswordReset\ConfirmPasswordResetCommandHandler;
use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommand;
use App\Features\Auth\Application\Commands\LoginUser\LoginUserCommandHandler;
use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommand;
use App\Features\Auth\Application\Commands\LogoutUser\LogoutUserCommandHandler;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Application\Commands\SendPasswordResetEmail\SendPasswordResetEmailCommand;
use App\Features\Auth\Application\Commands\SendPasswordResetEmail\SendPasswordResetEmailCommandHandler;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommandHandler;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQueryHandler;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Repositories\EloquentPasswordResetTokenRepository;
use App\Features\Auth\Infrastructure\Repositories\EloquentUserRepository;
use App\Features\Auth\Infrastructure\Security\LaravelPasswordHasher;
use App\Features\Auth\Infrastructure\Security\SanctumTokenCreator;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(PasswordResetTokenRepositoryInterface::class, EloquentPasswordResetTokenRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenCreatorInterface::class, SanctumTokenCreator::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $map = $this->app->make(HandlerMap::class);
        $map->register(RegisterUserCommand::class, RegisterUserCommandHandler::class);
        $map->register(LoginUserCommand::class, LoginUserCommandHandler::class);
        $map->register(LogoutUserCommand::class, LogoutUserCommandHandler::class);
        $map->register(UpdateUserPasswordCommand::class, UpdateUserPasswordCommandHandler::class);
        $map->register(SendPasswordResetEmailCommand::class, SendPasswordResetEmailCommandHandler::class);
        $map->register(ConfirmPasswordResetCommand::class, ConfirmPasswordResetCommandHandler::class);
        $map->register(GetUserByEmailQuery::class, GetUserByEmailQueryHandler::class);
    }
}
