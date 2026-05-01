# Database Operations

PostgreSQL runs in Docker and stores data in the named volume `padelito-backend_postgres-data`.

## Persistence Rules

These commands keep the database:

```bash
docker compose stop
docker compose down
docker compose up -d
docker compose up --build -d
```

This command deletes the database:

```bash
docker compose down -v
```

## Main Database

The main database is configured by `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=padelito
DB_USERNAME=padelito
DB_PASSWORD=change-me-local
```

## Test Database

The test database is `padelito_test`. On a fresh PostgreSQL volume it is created by:

```text
docker/postgres/init/01-create-test-database.sql
```

Postgres only runs files from `/docker-entrypoint-initdb.d` when the database volume is first initialized.

## Create The Test Database On An Existing Volume

If the volume existed before the init SQL was added, create the test database without deleting local data:

```bash
docker compose exec postgres createdb -U padelito padelito_test
```

If it already exists, Postgres will report that the database exists. That is safe.

## Run Migrations

```bash
docker compose exec app php artisan migrate --force
```

## Fresh Migration

This resets the current configured database schema. Use it carefully.

```bash
docker compose exec app php artisan migrate:fresh --force
```

## Connect With psql

```bash
docker compose exec postgres psql -U padelito -d padelito
```

For the test database:

```bash
docker compose exec postgres psql -U padelito -d padelito_test
```

## Full Local Reset

This removes containers and all named volumes, including database data:

```bash
docker compose down -v
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
```
