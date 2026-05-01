# Padelito Backend

Laravel backend for Padelito. The local development workflow is Docker-first: PHP, PostgreSQL, Redis, MinIO, Mailpit, and observability services run through Docker Compose.

## Requirements

- Docker with Docker Compose
- A local `.env` file copied from `.env.example`

## Quick Start

```bash
cp .env.example .env
docker compose up --build -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
```

The API is available at:

```bash
curl http://localhost:8001/up
curl http://localhost:8001/api/health
```

## Common Commands

```bash
docker compose up -d
docker compose stop
docker compose down
docker compose logs -f app nginx postgres redis
docker compose exec app sh
docker compose exec app composer test
docker compose exec app composer lint
```

## Database Persistence

PostgreSQL data is stored in the Docker volume `padelito-backend_postgres-data`, so it survives `docker compose stop`, `docker compose down`, and rebuilds.

Do not use `docker compose down -v` unless you intentionally want to delete local data. The `-v` flag removes volumes, including PostgreSQL, Redis, MinIO, Grafana, Loki, Prometheus, Tempo, and Alloy data.

## Documentation

- [Docker workflow](docs/docker.md)
- [Database operations](docs/database.md)
- [Testing](docs/testing.md)
- [Command reference](docs/commands.md)
