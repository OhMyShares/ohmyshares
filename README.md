# OhMyShares

Personal portfolio management.

# Installation

## Docker

Official image: [ohmyshares/ohmyshares](https://hub.docker.com/r/ohmyshares/ohmyshares) (`linux/amd64`, `linux/arm64`).

1. Clone repo to your server
2. Copy `.env.example` to `.env` and fill variables
3. Generate the application key and put it into `APP_KEY` in `.env`:
   ```bash
   docker compose run --rm --no-deps app php artisan key:generate --show
   ```
4. Run `docker compose up -d` (the image is pulled from Docker Hub)
5. Run migrations and seed default data:
   ```bash
   docker compose exec app php artisan migrate --force --seed
   ```
6. Create the first user with `docker compose exec app php artisan user:create` (the first user is an admin by default)

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