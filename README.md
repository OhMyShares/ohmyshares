# OhMyShares

Personal portfolio management.

# Installation

## Docker

Official image: [ohmyshares/ohmyshares](https://hub.docker.com/r/ohmyshares/ohmyshares) (`linux/amd64`, `linux/arm64`).

### Docker Compose

create `.env` file from `.env.example` and fill required variables

create docker-compose.yml:

```yaml
services:
  mysql:
    image: mysql:8.4
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - db-data:/var/lib/mysql
    healthcheck:
      test: [ "CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "--silent" ]
      interval: 5s
      timeout: 5s
      retries: 30
      start_period: 30s

  # Optional: set CACHE_DRIVER=memcached and MEMCACHED_HOST=memcached in .env
  # memcached:
  #   image: memcached:latest
  #   restart: unless-stopped

  app:
    image: ohmyshares/ohmyshares:latest
    restart: unless-stopped
    depends_on:
      mysql:
        condition: service_healthy
      # memcached:
      #   condition: service_started
    ports:
      - "8000:8000"
    env_file: .env
    volumes:
      - plugins:/var/www/oms/storage/plugins

volumes:
  db-data:
  plugins:
```

run:

```bash
docker compose up -d
```

### Manual Build

1. Clone repo to your server
2. Copy `.env.example` to `.env` and fill variables
3. Generate the application key and put it into `APP_KEY` in `.env`:
   ```bash
   docker compose run --rm --no-deps app php artisan key:generate --show
   ```
4. Run `docker compose up -d` (the image is pulled from Docker Hub)
5. Create the first user with `docker compose exec app php artisan user:create` (the first user is an admin by default)

Database migrations and default data are applied automatically on every container start.

Application logs are written to the container output: `docker compose logs -f app`.

Installed plugins are kept in the `plugins` volume.

To update to the latest version:

```bash
docker compose pull app
docker compose up -d
```

To pin a version, replace `latest` in `docker-compose.yml` with a release tag, e.g. `ohmyshares/ohmyshares:1.0.0`.

To build the image from source instead, run `docker compose build`.

## Plugins

Admins manage plugins at `/plugins`. Store discovery is configured with `PLUGINS_*` variables in `.env`.

---

# Contributing

See [CONTRIBUTING.md](docs/CONTRIBUTING.md) for setup, code style, and pull request guidelines.