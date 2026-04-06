<?php

declare(strict_types=1);

namespace Tests\Feature\Features\Player\Http;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use App\Shared\Domain\Contracts\ImageFetchInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\FeatureTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CreatePlayerProfileEndpointTest extends FeatureTestCase
{
    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3', ['url' => 'http://localhost']);

        $placeholderPng = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDQAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        Http::fake([
            'https://ui-avatars.com/api/*' => Http::response($placeholderPng, 200, ['Content-Type' => 'image/png']),
        ]);

        $register = $this->postJson('/api/v1/register', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => 'Password123!',
        ]);
        $register->assertStatus(201);

        $this->token = $register->json('token');
        $this->user = User::query()->whereKey($register->json('data.user.id'))->firstOrFail();
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_it_creates_a_player_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/player', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['player' => ['id']],
                'message',
            ])
            ->assertJsonPath('message', 'Player profile created successfully');

        $this->assertDatabaseHas('profiles', [
            'username' => 'jean_dupont',
            'level' => 'beginner',
        ]);
        $this->assertNotNull(DB::table('profiles')->where('id', $this->user->id)->value('avatar_url'));
        $this->assertCount(1, Storage::disk('s3')->allFiles('avatars/'.$this->user->id));
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->postJson('/api/v1/player', $this->validPayload())
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Conflict
    // -------------------------------------------------------------------------

    public function test_it_rejects_duplicate_profile_for_same_user(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload());

        $this->postJson('/api/v1/player', $this->validPayload(['username' => 'other_user']))
            ->assertStatus(409);
    }

    public function test_it_rejects_duplicate_username(): void
    {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload());

        Sanctum::actingAs($otherUser);
        $this->postJson('/api/v1/player', $this->validPayload())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    // -------------------------------------------------------------------------
    // Validation — required fields
    // -------------------------------------------------------------------------

    public function test_it_rejects_missing_username(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => null]))
            ->assertStatus(422);
    }

    public function test_it_rejects_missing_level(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['level' => null]))
            ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Validation — username format
    // -------------------------------------------------------------------------

    public function test_it_rejects_username_with_uppercase(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => 'Jean_Dupont']))
            ->assertStatus(422)
            ->assertJsonPath('error.details.username.0', 'The username may only contain lowercase letters, digits and underscores.');
    }

    public function test_it_rejects_username_too_long(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['username' => str_repeat('a', 31)]))
            ->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Validation — level enum
    // -------------------------------------------------------------------------

    public function test_it_rejects_invalid_level(): void
    {
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/player', $this->validPayload(['level' => 'god']))
            ->assertStatus(422);
    }

    public function test_it_accepts_all_valid_levels(): void
    {
        foreach (['beginner', 'intermediate', 'advanced', 'confirmed', 'expert'] as $i => $level) {
            $user = User::factory()->create();
            Sanctum::actingAs($user);
            $this->postJson('/api/v1/player', $this->validPayload([
                'username' => 'player_'.$i,
                'level' => $level,
            ]))->assertStatus(201);
        }
    }

    // -------------------------------------------------------------------------
    // Validation — HTML injection stripping
    // -------------------------------------------------------------------------

    public function test_it_strips_html_tags_from_bio(): void
    {
        Sanctum::actingAs($this->user);
        // strip_tags retire les balises, pas le texte à l'intérieur des scripts.
        $this->postJson('/api/v1/player', $this->validPayload([
            'bio' => '<p><strong>Passionné</strong> de padel</p>',
        ]))->assertStatus(201);

        $this->assertDatabaseHas('profiles', ['bio' => 'Passionné de padel']);
    }

    public function test_it_stores_avatar_from_uploaded_file(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Sanctum::actingAs($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/player', array_merge($this->validPayload([
                'username' => 'avatar_file_'.uniqid(),
            ]), [
                'avatar' => $this->minimalPngUploadedFile('avatar.png'),
            ]));

        $response->assertStatus(201);

        $avatarUrl = (string) DB::table('profiles')->where('id', $user->id)->value('avatar_url');
        $this->assertNotSame('', $avatarUrl);
        $this->assertCount(1, Storage::disk('s3')->allFiles('avatars/'.$user->id));
    }

    public function test_it_stores_avatar_from_remote_url_via_image_fetcher(): void
    {
        $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDQAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

        $this->app->bind(ImageFetchInterface::class, fn () => new class($png) implements ImageFetchInterface
        {
            public function __construct(private readonly string $bytes) {}

            public function fetch(string $url): string
            {
                return $this->bytes;
            }
        });

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Sanctum::actingAs($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/player', array_merge($this->validPayload([
                'username' => 'avatar_url_'.uniqid(),
            ]), [
                'avatar' => 'https://example.com/remote.png',
            ]));

        $response->assertStatus(201);

        $this->assertCount(1, Storage::disk('s3')->allFiles('avatars/'.$user->id));
        $this->assertNotNull(DB::table('profiles')->where('id', $user->id)->value('avatar_url'));
    }

    public function test_it_rejects_avatar_string_that_is_not_https(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Sanctum::actingAs($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/player', array_merge($this->validPayload([
                'username' => 'bad_avatar_url_'.uniqid(),
            ]), [
                'avatar' => 'http://example.com/x.png',
            ]));

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_it_rolls_back_s3_upload_when_profile_creation_fails(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Sanctum::actingAs($user);

        $u1 = 'rollback_u1_'.uniqid();
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/player', $this->validPayload(['username' => $u1]))
            ->assertStatus(201);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->post('/api/v1/player', array_merge($this->validPayload([
                'username' => 'rollback_u2_'.uniqid(),
            ]), [
                'avatar' => $this->minimalPngUploadedFile('second.png'),
            ]));

        $response->assertStatus(409);

        $this->assertCount(1, Storage::disk('s3')->allFiles('avatars/'.$user->id));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'jean_dupont',
            'displayName' => 'Jean Dupont',
            'level' => 'beginner',
            'dominantHand' => 'right',
            'preferredPosition' => 'back',
        ], $overrides);
    }

    private function minimalPngUploadedFile(string $filename): UploadedFile
    {
        $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDQAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        $tmp = tempnam(sys_get_temp_dir(), 'png');
        if ($tmp === false) {
            self::fail('Could not create temp file');
        }
        file_put_contents($tmp, $png);

        return new UploadedFile($tmp, $filename, 'image/png', null, true);
    }
}
