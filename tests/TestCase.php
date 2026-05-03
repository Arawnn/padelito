<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->forceTestingDatabase();

        parent::setUp();

        $this->assertTestingDatabaseIsUsed();
    }

    private function forceTestingDatabase(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'testing');
        $this->setEnvironmentVariable('DB_CONNECTION', 'pgsql');
        $this->setEnvironmentVariable('DB_DATABASE', 'padelito_test');
        $this->setEnvironmentVariable('DB_URL', '');
    }

    private function setEnvironmentVariable(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private function assertTestingDatabaseIsUsed(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($database !== 'padelito_test') {
            throw new LogicException("Tests must run against padelito_test, got {$database}.");
        }
    }
}
