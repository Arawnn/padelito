<?php

require __DIR__.'/../vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

$host = env_value('DB_TEST_HOST', env_value('DB_HOST', '127.0.0.1'));
$port = env_value('DB_TEST_PORT', env_value('DB_PORT', '5432'));
$database = env_value('DB_TEST_DATABASE', 'padelito_test');
$username = env_value('DB_TEST_USERNAME', env_value('DB_USERNAME', 'padelito'));
$password = env_value('DB_TEST_PASSWORD', env_value('DB_PASSWORD', 'change-me-local'));
$maintenanceDatabase = env_value('DB_TEST_MAINTENANCE_DATABASE', 'postgres');

if (! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    fwrite(STDERR, "Invalid test database name: {$database}\n");
    exit(1);
}

$dsn = "pgsql:host={$host};port={$port};dbname={$maintenanceDatabase};sslmode=disable";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $exception) {
    fwrite(STDERR, "Unable to connect to PostgreSQL for tests at {$host}:{$port}.\n");
    fwrite(STDERR, "Start it with: docker compose --env-file .env.docker up -d postgres\n");
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}

$statement = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
$statement->execute(['database' => $database]);

if ($statement->fetchColumn() !== false) {
    exit(0);
}

$quotedDatabase = '"'.str_replace('"', '""', $database).'"';
$pdo->exec("CREATE DATABASE {$quotedDatabase}");
