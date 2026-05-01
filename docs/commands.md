# Command Reference

## Setup

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
```

## Docker

```bash
docker compose up -d
docker compose up --build -d
docker compose stop
docker compose down
docker compose ps
docker compose logs -f app nginx postgres redis
docker compose exec app sh
```

## Destructive Docker Reset

```bash
docker compose down -v
```

This deletes Docker volumes. Do not use it if you want to keep local database data.

## Application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate --force
docker compose exec app php artisan migrate:fresh --force
```

## Tests And Quality

```bash
docker compose exec app composer test
docker compose exec app composer test:lint
docker compose exec app composer lint
docker compose exec app php artisan test
docker compose exec app php artisan test --filter it_registers_a_user
```

## Database

```bash
docker compose exec postgres psql -U padelito -d padelito
docker compose exec postgres psql -U padelito -d padelito_test
docker compose exec postgres createdb -U padelito padelito_test
```

## Health Checks

```bash
curl http://localhost:8001/up
curl http://localhost:8001/api/health
```

## Local Tools

```bash
open http://localhost:8025
open http://localhost:9001
open http://localhost:3000
```

If `open` is not available on your platform, paste the URL into your browser.
