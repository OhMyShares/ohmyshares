# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-10-19

### Added

- Background plugin installation and upgrade: installs run in the queue, progress is polled from the UI. New `PLUGINS_INSTALL_TIMEOUT` and `PLUGINS_INSTALL_WAIT` settings (`QUEUE_RETRY_AFTER` must be greater than the install timeout).
- Extended portfolio analytics, with new API data, charts and translations.
- Portfolio analytics can be viewed in any available currency without changing the portfolio currency.

### Fixed

- Broker report import no longer stalls in the queue: the import job has its own timeout (`REPORT_IMPORT_TIMEOUT`, must be less than `QUEUE_RETRY_AFTER`) and fails with a user-visible notice instead of staying reserved forever. Faster import of large reports (portfolio items are recalculated once per import instead of after every trade).

### Changed

- Frontend upgraded to Angular 22 and Taiga UI 5.

## [1.0.1] - 2026-10-10

### Added

- Automatic migrations and default data on container start.
- `LOG_STACK` environment variable to configure stack logging channels.

### Changed

- Memcached is no longer required.
- Portfolio index cache uses a plain cache key instead of cache tags, so any cache driver works.
- Removed unused dependencies.
- Docker configuration, Makefile and README updates.

## [1.0.0] - 2026-10-10

### Added

- Initial release: portfolio management, trades, analytics, sectors and tickers import, currency rates, plugin system, REST API with Swagger documentation, and the Angular frontend.

[Unreleased]: https://github.com/OhMyShares/ohmyshares/compare/1.0.2...HEAD
[1.0.2]: https://github.com/OhMyShares/ohmyshares/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/OhMyShares/ohmyshares/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/OhMyShares/ohmyshares/releases/tag/1.0.0
