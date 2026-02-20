<?php

// namespace App\Features\Auth\Infrastructure\Actions;


// use App\Features\Auth\Infrastructure\Models\User;
// use Laravel\Fortify\Contracts\ResetsUserPasswords;
// use App\Shared\Application\Bus\CommandBusInterface;
// use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;

// class ResetUserPassword implements ResetsUserPasswords
// {
    
//     public function __construct(
//         private CommandBusInterface $commandBus,
//     ) {}
//     /**
//      * Validate and reset the user's forgotten password.
//      *
//      * @param  array<string, string>  $input
//      */
//     public function reset(User $user, array $input): void
//     {
//         $this->commandBus->dispatch(new UpdateUserPasswordCommand(
//             userId: $user->id,
//             password: $input['password'],
//         ));
//     }
// }
