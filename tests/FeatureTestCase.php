<?php

namespace Tests;

use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Shared\Mother\Fake\FakeAvatarProvisioner;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->bind(AvatarProvisionerInterface::class, fn () => FakeAvatarProvisioner::thatSucceeds());
    }
}
