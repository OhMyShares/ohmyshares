<?php

namespace App\Services;

use App\Exceptions\PluginException;
use App\Models\Plugin;
use App\Models\Trade;
use DateTimeInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OhMyShares\Plugin\Dtos\Ticker as TickerDto;
use OhMyShares\Plugin\Dtos\Trade as TradeDto;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Enums\TradeType;
use Symfony\Component\Process\Process;
use Throwable;

class PluginService
{
    public const CATALOG_CACHE_KEY = 'plugins.catalog';

    public const STARS_CACHE_KEY = 'plugins.stars';

    public const PACKAGE_NAME_PATTERN = '/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*$/';

    public function path(string $suffix = ''): string
    {
        return rtrim(config('plugins.path'), '/').($suffix ? '/'.ltrim($suffix, '/') : '');
    }

    public function catalog(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget(self::CATALOG_CACHE_KEY);
        }
        $items = Cache::remember(self::CATALOG_CACHE_KEY, config('plugins.catalog_cache_ttl'), fn(): array => $this->fetchCatalog());

        $byPackage = Plugin::all()->keyBy('package');
        $byUrl = Plugin::all()->keyBy(fn(Plugin $p): string => $this->normalizeUrl($p->remote));

        foreach ($items as &$item) {
            /** @var Plugin|null $installed */
            $installed = $byPackage->get($item['name']) ?: $byUrl->get($this->normalizeUrl($item['url']));
            $item['installed_id'] = $installed?->id;
            $item['installed_version'] = $installed?->version;
            $item['is_approved'] = $this->isApproved($item['url']);
            $item['is_popular'] = $this->isPopular($item['stars']);
        }
        return array_values($items);
    }

    public function isApproved(?string $remote): bool
    {
        $official = $this->normalizeUrl(config('plugins.official_url'));
        $url = $this->normalizeUrl($remote);
        return $official !== ''
            && str_starts_with(strtolower(trim((string)$remote)), 'https://')
            && str_starts_with($url, $official.'/')
            && !str_contains($url, '..');
    }

    public function isPopular(?int $stars): bool
    {
        return $stars !== null && $stars > (int)config('plugins.popular_stars_threshold');
    }

    public function stars(?string $remote): ?int
    {
        $repo = $this->githubRepo($remote);
        if (!$repo) {
            return null;
        }
        $stars = Cache::remember(
            self::STARS_CACHE_KEY.'.'.$repo,
            config('plugins.catalog_cache_ttl'),
            fn(): int|false => $this->fetchStars($repo) ?? false,
        );
        return $stars === false ? null : $stars;
    }

    private function fetchStars(string $repo): ?int
    {
        try {
            $response = $this->github()->get('https://api.github.com/repos/'.$repo);
            if (!$response->successful()) {
                Log::warning('Plugins: GitHub repo '.$repo.' failed: '.$response->status().' '.$response->json('message'));
                return null;
            }
            return (int)$response->json('stargazers_count', 0);
        } catch (Throwable $e) {
            Log::warning('Plugins: GitHub repo '.$repo.' failed: '.$e->getMessage());
            return null;
        }
    }

    private function githubRepo(?string $remote): ?string
    {
        return preg_match('/^github\.com\/([\w.-]+\/[\w.-]+)$/', $this->normalizeUrl($remote), $matches) ? $matches[1] : null;
    }

    public function fetchCatalog(): array
    {
        $items = [];
        foreach ($this->fetchGithubTopic() as $item) {
            $items[$this->normalizeUrl($item['url'])] = $item;
        }
        foreach (config('plugins.registries', []) as $registry) {
            foreach ($this->fetchRegistry($registry) as $item) {
                $key = $this->normalizeUrl($item['url']);
                $items[$key] = array_merge($items[$key] ?? [], array_filter($item, fn($v): bool => $v !== null));
            }
        }
        foreach ($items as &$item) {
            $item = array_merge([
                'title' => $item['name'],
                'description' => null,
                'version' => null,
                'stars' => null,
                'updated_at' => null,
            ], $item);
        }
        unset($item);
        usort($items, fn(array $a, array $b): int => ($b['stars'] ?? 0) <=> ($a['stars'] ?? 0) ?: strcmp($a['name'], $b['name']));
        return $items;
    }

    public function fetchGithubTopic(): array
    {
        $topic = config('plugins.github_topic');
        if (!$topic) {
            return [];
        }
        try {
            $response = $this->github()->get('https://api.github.com/search/repositories', [
                'q' => 'topic:'.$topic,
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => 100,
            ]);
            if (!$response->successful()) {
                Log::warning('Plugins: GitHub search failed: '.$response->status().' '.$response->json('message'));
                return [];
            }
            $repos = $response->json('items', []);
        } catch (Throwable $e) {
            Log::warning('Plugins: GitHub search failed: '.$e->getMessage());
            return [];
        }

        $manifests = $this->fetchGithubManifests($repos);

        $items = [];
        foreach ($repos as $repo) {
            $manifest = $manifests[$repo['full_name']] ?? [];
            $items[] = [
                'name' => Arr::get($manifest, 'name', strtolower($repo['full_name'])),
                'title' => Arr::get($manifest, 'extra.ohmyshares.title', $repo['name']),
                'description' => Arr::get($manifest, 'description') ?: $repo['description'],
                'url' => $repo['html_url'],
                'version' => Arr::get($manifest, 'version'),
                'stars' => $repo['stargazers_count'] ?? 0,
                'updated_at' => $repo['pushed_at'] ?? null,
                'source' => 'github',
            ];
        }
        return $items;
    }

    private function fetchGithubManifests(array $repos): array
    {
        if (!$repos) {
            return [];
        }
        try {
            $responses = Http::pool(function (Pool $pool) use ($repos): void {
                foreach ($repos as $repo) {
                    $pool->as($repo['full_name'])->timeout(10)
                        ->get('https://raw.githubusercontent.com/'.$repo['full_name'].'/'.$repo['default_branch'].'/composer.json');
                }
            });
        } catch (Throwable $e) {
            Log::warning('Plugins: manifests fetch failed: '.$e->getMessage());
            return [];
        }
        $manifests = [];
        foreach ($responses as $fullName => $response) {
            if ($response instanceof Response && $response->successful()) {
                $data = json_decode($response->body(), true);
                if (is_array($data)) {
                    $manifests[$fullName] = $data;
                }
            }
        }
        return $manifests;
    }

    public function fetchRegistry(string $url): array
    {
        try {
            $response = Http::acceptJson()->timeout(15)->get($url);
            if (!$response->successful()) {
                Log::warning('Plugins: registry '.$url.' failed: '.$response->status());
                return [];
            }
            $plugins = $response->json('plugins', []);
        } catch (Throwable $e) {
            Log::warning('Plugins: registry '.$url.' failed: '.$e->getMessage());
            return [];
        }
        $items = [];
        foreach ($plugins as $plugin) {
            if (empty($plugin['url']) || empty($plugin['name'])) {
                continue;
            }
            $items[] = [
                'name' => $plugin['name'],
                'title' => $plugin['title'] ?? null,
                'description' => $plugin['description'] ?? null,
                'url' => $plugin['url'],
                'version' => $plugin['version'] ?? null,
                'stars' => null,
                'updated_at' => $plugin['updated_at'] ?? null,
                'source' => $url,
            ];
        }
        return $items;
    }

    public function install(string $remote): Plugin
    {
        return $this->build($remote, null);
    }

    public function upgrade(Plugin $plugin): Plugin
    {
        return $this->build($plugin->remote, $plugin);
    }

    public function uninstall(Plugin $plugin): bool
    {
        $instance = $plugin->getPlugin();
        if ($instance) {
            try {
                $instance->beforeUninstall();
            } catch (Throwable $e) {
                Log::error('Plugin '.$plugin->package.' beforeUninstall failed: '.$e->getMessage());
            }
        }
        if ($plugin->source && File::isDirectory($plugin->source) && !File::deleteDirectory($plugin->source)) {
            throw new PluginException('Unable to delete '.$plugin->source);
        }
        return (bool)$plugin->delete();
    }

    public function setEnabled(Plugin $plugin, bool $enabled): Plugin
    {
        if ($enabled && !$plugin->getPlugin()) {
            throw new PluginException('Plugin cannot be enabled: '.$plugin->getLoadError());
        }
        $plugin->is_enabled = $enabled;
        $plugin->save();
        return $plugin;
    }

    public function saveConfig(Plugin $plugin, array $config): Plugin
    {
        $schema = $plugin->config_schema;
        foreach ($schema as $key => $field) {
            if (!empty($field['required']) && (!isset($config[$key]) || $config[$key] === '')) {
                throw new PluginException('Config field "'.($field['label'] ?? $key).'" is required');
            }
        }
        $plugin->config = $config;
        $plugin->save();
        try {
            $plugin->getPlugin()?->onConfigSaved($config);
        } catch (Throwable $e) {
            Log::error('Plugin '.$plugin->package.' onConfigSaved failed: '.$e->getMessage());
        }
        return $plugin;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Plugin>
     */
    public function enabled()
    {
        return Plugin::enabled()->get()->filter(fn(Plugin $plugin): bool => $plugin->getPlugin() !== null);
    }

    public function onHistoryRequest(TickerDto $ticker, ?DateTimeInterface $start, ?DateTimeInterface $end): void
    {
        foreach ($this->enabled() as $plugin) {
            try {
                $plugin->getPlugin()->onHistoryRequest($ticker, $start, $end);
            } catch (Throwable $e) {
                Log::error('Plugin '.$plugin->package.' onHistoryRequest('.$ticker->ticker.') failed: '.$e->getMessage());
            }
        }
    }

    public function onPeriodic(): void
    {
        foreach ($this->enabled() as $plugin) {
            try {
                $plugin->getPlugin()->onPeriodic();
            } catch (Throwable $e) {
                Log::error('Plugin '.$plugin->package.' onPeriodic failed: '.$e->getMessage());
            }
        }
    }

    public function onTradeAdded(Trade $trade): void
    {
        $dto = $this->tradeDto($trade);
        foreach ($this->enabled() as $plugin) {
            try {
                $plugin->getPlugin()->onTradeAdded($dto);
            } catch (Throwable $e) {
                Log::error('Plugin '.$plugin->package.' onTradeAdded failed: '.$e->getMessage());
            }
        }
    }

    private function tradeDto(Trade $trade): TradeDto
    {
        $dto = new TradeDto;
        $dto->type = TradeType::from($trade->type);
        $dto->ticker = $trade->ticker;
        $dto->assetType = AssetType::from($trade->asset);
        $dto->cost = $trade->cost !== null ? (float)$trade->cost : null;
        $dto->shares = $trade->shares !== null ? (float)$trade->shares : null;
        $dto->sum = (float)$trade->sum;
        $dto->fee = (float)$trade->fee;
        $dto->currency = $trade->currency_code;
        $dto->timestamp = $trade->created_at->getTimestamp();
        $dto->notes = $trade->notes;
        $dto->portfolioUid = $trade->portfolio_uid;
        return $dto;
    }

    private function build(string $remote, ?Plugin $existing): Plugin
    {
        [$url, $ref] = $this->parseRemote($remote);
        $tmp = $this->path('.tmp/'.Str::random(8));
        File::ensureDirectoryExists($this->path('.tmp'));

        try {
            $this->fetchSource($url, $ref, $tmp);
            $composer = Plugin::readComposer($tmp);
            $package = Arr::get($composer, 'name');
            if (!$package || !preg_match(self::PACKAGE_NAME_PATTERN, $package)) {
                throw new PluginException('composer.json must contain a valid package "name"');
            }
            if (!Plugin::resolveClassName($composer)) {
                throw new PluginException('Unable to resolve plugin class: set "extra.ohmyshares.class" or "autoload.psr-4" in composer.json');
            }
            if ($existing instanceof \App\Models\Plugin && $existing->package !== $package) {
                throw new PluginException('Remote package name "'.$package.'" differs from installed "'.$existing->package.'"');
            }
            if (!$existing && Plugin::where('package', $package)->exists()) {
                throw new PluginException('Plugin "'.$package.'" is already installed');
            }
            $this->composerInstall($tmp);

            $target = $this->path($package);
            $this->swapDirectories($tmp, $target);
        } catch (Throwable $e) {
            File::deleteDirectory($tmp);
            throw $e instanceof PluginException ? $e : new PluginException($e->getMessage(), $e);
        }

        $plugin = $existing ?: new Plugin;
        $plugin->package = $package;
        $plugin->remote = $remote;
        $plugin->source = $target;
        if (!$plugin->loadPlugin()) {
            if (!$existing) {
                File::deleteDirectory($target);
            }
            throw new PluginException('Plugin failed to load: '.$plugin->getLoadError());
        }
        $instance = $plugin->getPlugin();
        $plugin->title = $instance->title();
        $plugin->version = Arr::get($composer, 'version') ?: $instance->version();
        if (!$existing) {
            $plugin->is_enabled = true;
        }
        $plugin->save();

        try {
            $instance->afterInstall();
        } catch (Throwable $e) {
            Log::error('Plugin '.$package.' afterInstall failed: '.$e->getMessage());
            $plugin->is_enabled = false;
            $plugin->save();
            throw new PluginException('Plugin installed but afterInstall() failed, plugin disabled: '.$e->getMessage(), $e);
        }
        return $plugin;
    }

    public function parseRemote(string $remote): array
    {
        $remote = trim($remote);
        $ref = null;
        if (str_contains($remote, '#')) {
            [$remote, $ref] = explode('#', $remote, 2);
            $ref = trim($ref) ?: null;
        }
        if ($ref && !preg_match('/^[\w.\/-]+$/', $ref)) {
            throw new PluginException('Invalid git reference "'.$ref.'"');
        }
        if (str_starts_with($remote, '/') && File::isDirectory($remote)) {
            return [realpath($remote), $ref];
        }
        $scheme = strtolower((string)parse_url($remote, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https']) || !filter_var($remote, FILTER_VALIDATE_URL)) {
            throw new PluginException('Remote must be an http(s) git repository URL, optionally with #branch-or-tag');
        }
        return [$remote, $ref];
    }

    private function fetchSource(string $url, ?string $ref, string $target): void
    {
        $command = [config('plugins.git_bin'), 'clone', '--depth', '1', '--quiet'];
        if ($ref) {
            array_push($command, '--branch', $ref);
        }
        array_push($command, '--', $url, $target);
        $this->run($command, $this->path());
        File::deleteDirectory($target.'/.git');
    }

    private function composerInstall(string $cwd): void
    {
        $this->run([
            config('plugins.composer_bin'), 'install',
            '--no-dev', '--no-interaction', '--no-progress', '--prefer-dist', '--optimize-autoloader',
        ], $cwd);
    }

    private function swapDirectories(string $source, string $target): void
    {
        $backup = $target.'.bak';
        File::deleteDirectory($backup);
        if (File::isDirectory($target) && !File::moveDirectory($target, $backup)) {
            throw new PluginException('Unable to move '.$target);
        }
        File::ensureDirectoryExists(dirname($target));
        if (!File::moveDirectory($source, $target)) {
            if (File::isDirectory($backup)) {
                File::moveDirectory($backup, $target);
            }
            throw new PluginException('Unable to move plugin into '.$target);
        }
        File::deleteDirectory($backup);
    }

    private function run(array $command, string $cwd): string
    {
        $home = $this->path('.home');
        File::ensureDirectoryExists($home);
        $process = new Process($command, $cwd, [
            'HOME' => $home,
            'COMPOSER_HOME' => $home.'/composer',
            'COMPOSER_ALLOW_SUPERUSER' => '1',
            'COMPOSER_NO_INTERACTION' => '1',
            'COMPOSER_MEMORY_LIMIT' => '-1',
            'GIT_TERMINAL_PROMPT' => '0',
            'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
        ], null, (float)config('plugins.process_timeout'));
        $process->run();
        if (!$process->isSuccessful()) {
            $output = trim($process->getErrorOutput()) ?: trim($process->getOutput());
            throw new PluginException($command[0].' '.$command[1].' failed: '.Str::limit($output, 1000));
        }
        return $process->getOutput();
    }

    private function github()
    {
        $request = Http::acceptJson()->timeout(15)->withHeaders([
            'User-Agent' => 'OhMyShares',
            'X-GitHub-Api-Version' => '2022-11-28',
        ]);
        $token = config('plugins.github_token');
        return $token ? $request->withToken($token) : $request;
    }

    private function normalizeUrl(?string $url): string
    {
        $url = strtolower(trim((string)$url));
        $url = preg_replace('/^[a-z]+:\/\//', '', $url);
        $url = preg_replace('/^git@([^:]+):/', '$1/', $url);
        $url = preg_replace('/#.*$/', '', $url);
        $url = preg_replace('/\.git$/', '', rtrim($url, '/'));
        return $url;
    }
}
