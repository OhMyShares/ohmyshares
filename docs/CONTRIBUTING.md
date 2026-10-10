# Contributing to OhMyShares

Thanks for your interest in contributing! This document covers how to set up the project locally and the guidelines to
follow when submitting changes.

## Getting Started

1. Fork the repository and clone your fork
   ```
   git clone https://github.com/<your-username>/ohmyshares.git
   ```
2. Copy `.env.example` to `.env` and fill in the required variables
3. Install dependencies and generate the application key
   ```
   docker compose -f docker-compose.dev.yml run --rm --no-deps app composer install
   docker compose -f docker-compose.dev.yml run --rm --no-deps app php artisan key:generate
   cd frontend && npm ci
   ```
4. Start the development stack
   ```
   make dev
   ```
    - Backend/API: `http://localhost:8000`
    - Frontend (with live reload): `http://localhost:4200`
    - API documentation: `http://localhost:8000/api/documentation`
5. In another terminal, run migrations and seed default data (sectors)
   ```
   docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
   ```

## Queue and scheduler

The queue worker and the scheduler run inside the `app` container via supervisor
(see `docker/supervisor/supervisord.conf`), no extra setup is required.

## Development Guidelines

### Code Style

- PHP code must follow PSR-1, for the PHP version specified in `composer.json`.
- Do not add comments inside methods unless there is a specific reason the code cannot be made self-explanatory.
- Do not use `assert()` for type assertions or runtime checks; rely on PHPStan's static analysis instead.
- Frontend UI is built with Angular; follow the existing patterns in `frontend/src`.

### Tests

Run the full test suite before submitting a change:

```
php artisan test
```

Run a specific group or file:

```
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test tests/Feature/ApiTest
```

Add or update tests for any behavioral change. Do not remove or weaken existing tests without a clear justification in
your pull request.

### Plugins

If your change affects the plugin system, be aware that admins manage plugins at `/plugins` and store discovery is
configured via `PLUGINS_*` environment variables.

## Submitting Changes

1. Create a feature branch from `main` (or the repository's default branch)
2. Keep pull requests focused on a single change; avoid bundling unrelated fixes
3. Make sure `php artisan test` passes and the frontend builds (`cd frontend && npm run build`)
4. Write a clear PR description explaining the motivation and the change
5. Link any related issue in the PR description

## Reporting Issues

When filing a bug report, please include:

- Steps to reproduce
- Expected vs. actual behavior
- Relevant logs, error messages, or screenshots
- Your environment (OS, PHP version, browser, etc.)

## Security Issues

Please do not open a public issue for security vulnerabilities. See [SECURITY.md](SECURITY.md) for how to report them
privately.
