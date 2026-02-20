<?php

// declare(strict_types=1);

// namespace App\Features\Auth\Infrastructure\Http\Controllers\Settings;

// use Illuminate\Http\Request;
// use Illuminate\Http\RedirectResponse;
// use App\Shared\Application\Bus\CommandBusInterface;
// use App\Shared\Infrastructure\Http\Controllers\Controller;

// class LoginController extends Controller
// {
//     public function __construct(
//         private CommandBusInterface $commandBus,
//     ) {}

//     public function __invoke(Request $request): RedirectResponse
//     {
//         $this->commandBus->dispatch(new LoginUserCommand(
//             email: $request->email,
//             password: $request->password,
//         ));
//     }
// }