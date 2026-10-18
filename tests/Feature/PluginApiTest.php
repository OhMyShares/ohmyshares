<?php

namespace Tests\Feature;

use App\Jobs\InstallPlugin;
use App\Models\Plugin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PluginApiTest extends TestCase
{
    use DatabaseTransactions;

    private string $workDir;

    protected function setUp(): void
    {
        parent::setUp();
        $finder = new ExecutableFinder;
        if (!$finder->find('git') || !$finder->find('composer')) {
            $this->markTestSkipped('git and composer are required');
        }
        $this->workDir = sys_get_temp_dir().'/oms-plugin-test-'.Str::random(8);
        config(['plugins.path' => $this->workDir.'/plugins']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->workDir);
        parent::tearDown();
    }

    public function testNonAdminIsForbidden()
    {
        $user = $this->createUser(User::ROLE_USER);
        $this->actingAs($user, 'api')->getJson('/api/plugins')->assertStatus(403);
        $this->actingAs($user, 'api')->postJson('/api/plugins', ['remote' => 'https://example.com/x'])->assertStatus(403);
    }

    public function testInstallConfigureUpgradeUninstall()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $repo = $this->createPluginRepo('acme/test-plugin', '1.0.0');

        $response = $this->actingAs($admin, 'api')->postJson('/api/plugins', ['remote' => $repo]);
        $response->assertStatus(202)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('package', 'acme/test-plugin')
            ->assertJsonPath('version', '1.0.0')
            ->assertJsonPath('title', 'Test Plugin');
        $installationId = $response->json('id');
        $id = $response->json('plugin_id');
        $this->assertNotNull($id);
        $this->assertFileExists($this->workDir.'/plugins/acme/test-plugin/vendor/autoload.php');
        $this->assertDirectoryDoesNotExist($this->workDir.'/plugins/acme/test-plugin/.git');

        $this->actingAs($admin, 'api')->getJson('/api/plugins/installations/'.$installationId)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('plugin_id', $id);
        $this->actingAs($admin, 'api')->getJson('/api/plugins/installations/unknown-id')
            ->assertStatus(404);

        $this->actingAs($admin, 'api')->getJson('/api/plugins/'.$id)
            ->assertStatus(200)
            ->assertJsonPath('is_enabled', true)
            ->assertJsonPath('is_loaded', true)
            ->assertJsonPath('is_approved', false)
            ->assertJsonPath('stars', null)
            ->assertJsonPath('is_popular', false)
            ->assertJsonPath('config_schema.api_key.required', true);

        $this->actingAs($admin, 'api')->postJson('/api/plugins', ['remote' => $repo])
            ->assertStatus(202)
            ->assertJsonPath('status', 'failed')
            ->assertJsonPath('error', 'Plugin "acme/test-plugin" is already installed');

        $this->actingAs($admin, 'api')->putJson('/api/plugins/'.$id.'/config', ['config' => ['api_key' => '']])
            ->assertStatus(422);
        $this->actingAs($admin, 'api')->putJson('/api/plugins/'.$id.'/config', ['config' => ['api_key' => 'secret']])
            ->assertStatus(200)
            ->assertJsonPath('config.api_key', 'secret');

        $this->actingAs($admin, 'api')->patchJson('/api/plugins/'.$id.'/enable', ['is_enabled' => false])
            ->assertStatus(200)
            ->assertJsonPath('is_enabled', false);
        $this->assertNotContains($id, app(\App\Services\PluginService::class)->enabled()->pluck('id')->all());

        $this->bumpPluginVersion($repo, '1.1.0');
        $this->actingAs($admin, 'api')->postJson('/api/plugins/'.$id.'/upgrade')
            ->assertStatus(202)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('version', '1.1.0');
        $this->actingAs($admin, 'api')->getJson('/api/plugins/'.$id)
            ->assertStatus(200)
            ->assertJsonPath('version', '1.1.0')
            ->assertJsonPath('is_enabled', false)
            ->assertJsonPath('config.api_key', 'secret');

        $list = $this->actingAs($admin, 'api')->getJson('/api/plugins')->assertStatus(200);
        $this->assertContains($id, $list->json('*.id'));

        $this->actingAs($admin, 'api')->deleteJson('/api/plugins/'.$id)->assertStatus(200);
        $this->assertNull(Plugin::find($id));
        $this->assertDirectoryDoesNotExist($this->workDir.'/plugins/acme/test-plugin');
    }

    public function testInstallIsDispatchedToQueue()
    {
        Queue::fake();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/plugins', ['remote' => 'https://github.com/acme/queued-plugin']);

        $response->assertStatus(202)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('remote', 'https://github.com/acme/queued-plugin');
        Queue::assertPushed(InstallPlugin::class);

        $this->actingAs($admin, 'api')->getJson('/api/plugins/installations/'.$response->json('id'))
            ->assertStatus(200)
            ->assertJsonPath('status', 'pending');
        $this->assertNull(Plugin::where('package', 'acme/queued-plugin')->first());
    }

    public function testInstallRejectsInvalidRemote()
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $this->actingAs($admin, 'api')->postJson('/api/plugins', ['remote' => 'ftp://example.com/repo'])
            ->assertStatus(422);
    }

    private function createUser(int $role): User
    {
        $user = User::create([
            'name' => 'Plugin Tester',
            'email' => 'plugin_test_'.Str::random(6).'@ohmyshares.com',
            'password' => bcrypt('test_password'),
            'locale' => 'en',
        ]);
        $user->role = $role;
        $user->save();
        return $user;
    }

    private function createPluginRepo(string $package, string $version): string
    {
        $dir = $this->workDir.'/repo';
        File::ensureDirectoryExists($dir.'/src');
        $this->writeManifest($dir, $package, $version);
        File::put($dir.'/src/Plugin.php', <<<'PHP'
<?php
namespace Acme\TestPlugin;

use OhMyShares\Plugin\BasePlugin;

class Plugin extends BasePlugin
{
    public function title(): string
    {
        return 'Test Plugin';
    }
}
PHP);
        $this->git($dir, ['init', '-q', '-b', 'main']);
        $this->git($dir, ['add', '.']);
        $this->git($dir, ['commit', '-q', '-m', 'v'.$version]);
        return $dir;
    }

    private function bumpPluginVersion(string $dir, string $version): void
    {
        $this->writeManifest($dir, 'acme/test-plugin', $version);
        $this->git($dir, ['commit', '-q', '-am', 'v'.$version]);
    }

    private function writeManifest(string $dir, string $package, string $version): void
    {
        File::put($dir.'/composer.json', json_encode([
            'name' => $package,
            'description' => 'Fixture plugin',
            'version' => $version,
            'autoload' => ['psr-4' => ['Acme\\TestPlugin\\' => 'src/']],
            'extra' => ['ohmyshares' => [
                'class' => 'Acme\\TestPlugin\\Plugin',
                'config' => ['api_key' => ['type' => 'string', 'label' => 'API key', 'required' => true]],
            ]],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function git(string $cwd, array $args): void
    {
        $process = new Process(array_merge(['git'], $args), $cwd, [
            'GIT_AUTHOR_NAME' => 'test', 'GIT_AUTHOR_EMAIL' => 'test@example.com',
            'GIT_COMMITTER_NAME' => 'test', 'GIT_COMMITTER_EMAIL' => 'test@example.com',
        ]);
        $process->mustRun();
    }
}
