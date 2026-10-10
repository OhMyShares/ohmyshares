<?php

namespace Tests\Unit;

use App\Exceptions\PluginException;
use App\Models\Plugin;
use App\Services\PluginService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PluginServiceTest extends TestCase
{
    private PluginService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PluginService;
    }

    public function testResolveClassNameFromManifest()
    {
        $composer = [
            'autoload' => ['psr-4' => ['Vendor\\Foo\\' => 'src/']],
            'extra' => ['ohmyshares' => ['class' => '\\Vendor\\Foo\\Custom']],
        ];
        $this->assertSame('Vendor\\Foo\\Custom', Plugin::resolveClassName($composer));
    }

    public function testResolveClassNameFallsBackToPsr4()
    {
        $composer = ['autoload' => ['psr-4' => ['Vendor\\Foo\\' => 'src/']]];
        $this->assertSame('Vendor\\Foo\\Plugin', Plugin::resolveClassName($composer));
        $this->assertNull(Plugin::resolveClassName([]));
    }

    public function testParseRemoteAcceptsHttpsWithRef()
    {
        $this->assertSame(
            ['https://github.com/vendor/plugin', 'v1.2.0'],
            $this->service->parseRemote('https://github.com/vendor/plugin#v1.2.0')
        );
        $this->assertSame(
            ['https://gitlab.com/vendor/plugin.git', null],
            $this->service->parseRemote(' https://gitlab.com/vendor/plugin.git ')
        );
    }

    public function testParseRemoteRejectsUnsupportedSchemes()
    {
        $this->expectException(PluginException::class);
        $this->service->parseRemote('ssh://git@github.com/vendor/plugin');
    }

    public function testParseRemoteRejectsInvalidRef()
    {
        $this->expectException(PluginException::class);
        $this->service->parseRemote('https://github.com/vendor/plugin#v1; rm -rf /');
    }

    public function testFetchCatalogMergesGithubTopicAndRegistry()
    {
        config([
            'plugins.github_topic' => 'ohmyshares-plugin',
            'plugins.registries' => ['https://example.com/registry.json'],
        ]);

        Http::fake([
            'api.github.com/search/repositories*' => Http::response(['items' => [
                [
                    'full_name' => 'acme/yahoo-plugin',
                    'name' => 'yahoo-plugin',
                    'description' => 'Repo description',
                    'html_url' => 'https://github.com/acme/yahoo-plugin',
                    'default_branch' => 'main',
                    'stargazers_count' => 5,
                    'pushed_at' => '2024-01-01T00:00:00Z',
                ],
                [
                    'full_name' => 'acme/no-manifest',
                    'name' => 'no-manifest',
                    'description' => null,
                    'html_url' => 'https://github.com/acme/no-manifest',
                    'default_branch' => 'master',
                    'stargazers_count' => 1,
                    'pushed_at' => null,
                ],
            ]]),
            'raw.githubusercontent.com/acme/yahoo-plugin/main/composer.json' => Http::response(json_encode([
                'name' => 'acme/yahoo-finance-plugin',
                'description' => 'Manifest description',
                'version' => '1.0.0',
                'extra' => ['ohmyshares' => ['title' => 'Yahoo Finance']],
            ])),
            'raw.githubusercontent.com/acme/no-manifest/*' => Http::response('', 404),
            'example.com/registry.json' => Http::response(['plugins' => [
                [
                    'name' => 'acme/yahoo-finance-plugin',
                    'url' => 'https://github.com/acme/yahoo-plugin.git',
                    'version' => '1.1.0',
                ],
                [
                    'name' => 'other/gitlab-plugin',
                    'title' => 'GitLab hosted',
                    'url' => 'https://gitlab.com/other/gitlab-plugin',
                ],
                ['name' => 'broken/no-url'],
            ]]),
        ]);

        $items = collect($this->service->fetchCatalog())->keyBy('name');

        $this->assertCount(3, $items);

        $yahoo = $items->get('acme/yahoo-finance-plugin');
        $this->assertSame('Yahoo Finance', $yahoo['title']);
        $this->assertSame('Manifest description', $yahoo['description']);
        $this->assertSame('1.1.0', $yahoo['version']);
        $this->assertSame(5, $yahoo['stars']);

        $noManifest = $items->get('acme/no-manifest');
        $this->assertSame('no-manifest', $noManifest['title']);
        $this->assertNull($noManifest['version']);

        $gitlab = $items->get('other/gitlab-plugin');
        $this->assertSame('https://example.com/registry.json', $gitlab['source']);
        $this->assertNull($gitlab['stars']);
    }

    public function testFetchCatalogSurvivesGithubFailure()
    {
        config(['plugins.github_topic' => 'ohmyshares-plugin', 'plugins.registries' => []]);
        Http::fake(['api.github.com/*' => Http::response(['message' => 'rate limited'], 403)]);

        $this->assertSame([], $this->service->fetchCatalog());
    }

    public function testIsApprovedOnlyForOfficialGithubOrganization()
    {
        config(['plugins.official_url' => 'https://github.com/OhMyShares/']);

        $this->assertTrue($this->service->isApproved('https://github.com/OhMyShares/cbr-currency-rates-plugin'));
        $this->assertTrue($this->service->isApproved('https://github.com/ohmyshares/cbr-currency-rates-plugin.git#v1.0.0'));
        $this->assertFalse($this->service->isApproved('http://github.com/OhMyShares/cbr-currency-rates-plugin'));
        $this->assertFalse($this->service->isApproved('https://github.com/OhMyShares'));
        $this->assertFalse($this->service->isApproved('https://github.com/OhMyShares-fake/plugin'));
        $this->assertFalse($this->service->isApproved('https://github.com.evil.com/OhMyShares/plugin'));
        $this->assertFalse($this->service->isApproved('https://evil@github.com/OhMyShares/plugin'));
        $this->assertFalse($this->service->isApproved('https://github.com/OhMyShares/../evil/plugin'));
        $this->assertFalse($this->service->isApproved('https://gitlab.com/OhMyShares/plugin'));
        $this->assertFalse($this->service->isApproved('/var/plugins/OhMyShares/plugin'));
        $this->assertFalse($this->service->isApproved(null));
    }

    public function testIsPopularWhenStarsExceedThreshold()
    {
        config(['plugins.popular_stars_threshold' => 10]);

        $this->assertTrue($this->service->isPopular(11));
        $this->assertFalse($this->service->isPopular(10));
        $this->assertFalse($this->service->isPopular(0));
        $this->assertFalse($this->service->isPopular(null));
    }

    public function testStarsFetchedFromGithubAndCached()
    {
        Http::fake([
            'api.github.com/repos/acme/starred-plugin' => Http::response(['stargazers_count' => 42]),
            'api.github.com/repos/acme/missing-plugin' => Http::response(['message' => 'Not Found'], 404),
        ]);

        $this->assertSame(42, $this->service->stars('https://github.com/acme/starred-plugin.git#v1'));
        $this->assertSame(42, $this->service->stars('https://github.com/acme/starred-plugin'));
        $this->assertNull($this->service->stars('https://github.com/acme/missing-plugin'));
        $this->assertNull($this->service->stars('https://github.com/acme/missing-plugin'));
        $this->assertNull($this->service->stars('https://gitlab.com/acme/starred-plugin'));
        $this->assertNull($this->service->stars('/var/plugins/acme'));
        Http::assertSentCount(2);
    }
}
