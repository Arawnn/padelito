<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Onboarding\Application\RegisterPlayer;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerCommand;
use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerCommandHandler;
use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerResult;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommandHandler;
use App\Features\Player\Domain\Services\UsernameGeneratorService;
use App\Features\Player\Domain\ValueObjects\Id;
use Tests\Shared\Mother\Fake\FakeAvatarProvisioner;
use Tests\Shared\Mother\Fake\FakePasswordHasher;
use Tests\Shared\Mother\Fake\FakeUuidGenerator;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\Fake\InMemoryUserRepository;
use Tests\Shared\Mother\Fake\SpyEventDispatcher;
use Tests\Shared\Mother\UserMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RegisterPlayerCommandHandlerTest extends TestCase
{
    private const NAME = 'John Doe';

    private const EMAIL = 'john@example.com';

    private const PASSWORD = 'Password123!';

    private InMemoryUserRepository $userRepository;

    private InMemoryPlayerRepository $playerRepository;

    private SpyEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new InMemoryUserRepository;
        $this->playerRepository = new InMemoryPlayerRepository;
        $this->eventDispatcher = new SpyEventDispatcher;
    }

    public function test_it_returns_a_register_player_result(): void
    {
        $result = $this->makeHandler()($this->validCommand());

        $this->assertInstanceOf(RegisterPlayerResult::class, $result);
        $this->assertEquals(self::EMAIL, $result->email);
        $this->assertEquals(self::NAME, $result->name);
        $this->assertNotEmpty($result->userId);
    }

    public function test_it_persists_the_user(): void
    {
        $this->makeHandler()($this->validCommand());

        $this->assertNotNull($this->userRepository->findByEmail(
            \App\Features\Auth\Domain\ValueObjects\Email::fromString(self::EMAIL)
        ));
    }

    public function test_it_persists_the_player_profile(): void
    {
        $result = $this->makeHandler()(new RegisterPlayerCommand(
            name: 'Jean Dupont',
            email: 'jean@example.com',
            password: self::PASSWORD,
        ));

        $player = $this->playerRepository->findById(Id::fromString($result->userId));

        $this->assertNotNull($player);
        $this->assertEquals('jean_dupont', $player->username()->value());
        $this->assertEquals('Jean Dupont', $player->identity()?->displayName()?->value());
    }

    public function test_it_propagates_exception_if_user_registration_fails(): void
    {
        $this->expectException(UserAlreadyExistsException::class);

        $this->userRepository->save(
            UserMother::create()->withEmail(self::EMAIL)->build()
        );

        $this->makeHandler()($this->validCommand());
    }

    public function test_it_does_not_create_player_profile_if_user_registration_fails(): void
    {
        $this->userRepository->save(
            UserMother::create()->withEmail(self::EMAIL)->build()
        );

        try {
            $this->makeHandler()($this->validCommand());
        } catch (UserAlreadyExistsException) {
            // expected — exception is thrown before player creation is attempted
        }

        // FakeUuidGenerator always returns 00000000-0000-0000-0000-000000000000,
        // but since the exception fires before UUID generation, no player is persisted.
        $this->assertNull($this->playerRepository->findById(
            Id::fromString('00000000-0000-0000-0000-000000000000')
        ));
    }

    private function validCommand(): RegisterPlayerCommand
    {
        return new RegisterPlayerCommand(
            name: self::NAME,
            email: self::EMAIL,
            password: self::PASSWORD,
        );
    }

    private function makeHandler(): RegisterPlayerCommandHandler
    {
        $registerUserHandler = new RegisterUserCommandHandler(
            userRepository: $this->userRepository,
            passwordHasher: new FakePasswordHasher,
            uuidGenerator: new FakeUuidGenerator,
            eventDispatcher: $this->eventDispatcher,
        );

        $initializePlayerProfileHandler = new InitializePlayerProfileCommandHandler(
            playerRepository: $this->playerRepository,
            usernameGenerator: new UsernameGeneratorService($this->playerRepository),
            avatarProvisioner: FakeAvatarProvisioner::thatSucceeds(),
            eventDispatcher: $this->eventDispatcher,
        );

        return new RegisterPlayerCommandHandler(
            registerUserHandler: $registerUserHandler,
            initializePlayerProfileHandler: $initializePlayerProfileHandler,
        );
    }
}
