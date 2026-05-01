# Testing

Tests are expected to run inside the `app` container.

## Full Test Suite

```bash
docker compose exec app composer test
```

This runs:

```bash
docker compose exec app php artisan config:clear --ansi
docker compose exec app composer test:lint
docker compose exec app php artisan test
```

## Lint Only

```bash
docker compose exec app composer test:lint
```

## Fix Formatting

```bash
docker compose exec app composer lint
```

## Run One Test File

```bash
docker compose exec app php artisan test tests/Feature/Features/Onboarding/Http/RegisterEndpointTest.php
```

## Run Tests By Filter

```bash
docker compose exec app php artisan test --filter it_registers_a_user
```

## Test Database

Tests use PostgreSQL database `padelito_test`, configured in `phpunit.xml`.

If the test database is missing on an existing volume, create it without deleting local data:

```bash
docker compose exec postgres createdb -U padelito padelito_test
```

## CI

GitHub Actions runs tests outside Docker with a PostgreSQL service. The workflow sets database host and credentials explicitly for CI.

```bash
./vendor/bin/phpunit
```

Local development should still use Docker commands.
