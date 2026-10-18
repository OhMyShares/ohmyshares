<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugins storage
    |--------------------------------------------------------------------------
    |
    | Directory where installed plugins are stored. Each plugin lives in
    | its own sub-directory named after the composer package name.
    |
    */
    'path' => storage_path('plugins'),

    /*
    |--------------------------------------------------------------------------
    | Plugin store discovery
    |--------------------------------------------------------------------------
    |
    | Plugins are discovered by GitHub repository topic. Any public repository
    | tagged with this topic is listed in the store automatically.
    | Optionally a GitHub token can be provided to raise API rate limits.
    |
    | Additional registries are plain JSON files (e.g. hosted on GitHub Pages,
    | GitLab or any static hosting) with the following structure:
    | {"plugins": [{"name": "vendor/package", "title": "...", "description": "...",
    |               "url": "https://github.com/vendor/package", "version": "1.0.0"}]}
    |
    */
    'github_topic' => env('PLUGINS_GITHUB_TOPIC', 'ohmyshares-plugin'),
    'github_token' => env('PLUGINS_GITHUB_TOKEN'),
    'registries' => array_values(array_filter(array_map('trim', explode(',', env('PLUGINS_REGISTRIES', ''))))),
    'catalog_cache_ttl' => (int)env('PLUGINS_CATALOG_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Trust badges
    |--------------------------------------------------------------------------
    |
    | Plugins installed from the official source are marked as "approved" and
    | installed without a security warning. Plugins having more GitHub stars
    | than the threshold are marked as "popular".
    |
    */
    'official_url' => env('PLUGINS_OFFICIAL_URL', 'https://github.com/OhMyShares/'),
    'popular_stars_threshold' => (int)env('PLUGINS_POPULAR_STARS_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Build tools
    |--------------------------------------------------------------------------
    */
    'git_bin' => env('PLUGINS_GIT_BIN', 'git'),
    'composer_bin' => env('PLUGINS_COMPOSER_BIN', 'composer'),
    'process_timeout' => (int)env('PLUGINS_PROCESS_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Background installation
    |--------------------------------------------------------------------------
    |
    | Installing a plugin clones its repository and runs composer, which can
    | take minutes. The work is done in a queued job, so these limits must be
    | greater than the time a single install may take. install_timeout is the
    | job timeout and the lock lifetime, install_wait is how long a job waits
    | for another installation to finish before giving up.
    |
    */
    'install_timeout' => (int)env('PLUGINS_INSTALL_TIMEOUT', 1500),
    'install_wait' => (int)env('PLUGINS_INSTALL_WAIT', 300),
    'installation_ttl' => (int)env('PLUGINS_INSTALLATION_TTL', 3600),

];
