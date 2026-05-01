# Docker Workflow

This project assumes the backend runs in Docker locally. The application containers receive environment variables from `./.env` through `env_file` in `docker-compose.yml`.

## Services

- `app`: PHP-FPM Laravel application
- `nginx`: HTTP entrypoint on `localhost:8001`
- `worker`: Laravel queue worker
- `scheduler`: Laravel scheduler
- `postgres`: PostgreSQL database
- `redis`: Redis cache, queue, and session backend
- `minio`: S3-compatible local storage
- `mailpit`: local SMTP inbox on `localhost:8025`
- `prometheus`, `grafana`, `loki`, `tempo`, `alloy`, `otel-collector`, `cadvisor`: observability stack

## Start The Stack

```bash
docker compose up --build -d
```

Use this after changing the `Dockerfile`, PHP extensions, or Compose configuration.

## Start Without Rebuilding

```bash
docker compose up -d
```

## Stop Without Data Loss

```bash
docker compose stop
```

This stops containers but keeps them and all volumes.

## Remove Containers Without Data Loss

```bash
docker compose down
```

This removes containers and networks but keeps named volumes such as PostgreSQL data.

## Destructive Reset

```bash
docker compose down -v
```

This deletes named volumes. Use it only when you intentionally want a clean local environment.

## Logs

```bash
docker compose logs -f app nginx postgres redis
```

For one service:

```bash
docker compose logs -f app
```

## Shell Into The App Container

```bash
docker compose exec app sh
```

## Rebuild After Permission Or Build Context Issues

Generated testing files under `storage/framework/testing` are ignored by Docker builds through `.dockerignore`. If a local permission issue remains, remove or fix ownership of that generated folder, then rebuild.

```bash
docker compose up --build -d
```
