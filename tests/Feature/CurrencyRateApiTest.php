<?php

namespace Tests\Feature;

use App\Models\CurrencyRate;
use App\Models\Plugin;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\PluginService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use OhMyShares\Plugin\BasePlugin;
use OhMyShares\Plugin\CurrencyRatesProviderInterface;
use OhMyShares\Plugin\Dtos\CurrencyRate as CurrencyRateDto;
use OhMyShares\Plugin\PluginInterface;
use Tests\TestCase;

class CurrencyRateApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        CurrencyRate::query()->delete();
    }

    public function testNonAdminIsForbidden(): void
    {
        $user = $this->createUser(User::ROLE_USER);
        $this->actingAs($user, 'api')->getJson('/api/currency-rates')->assertStatus(403);
        $this->actingAs($user, 'api')->postJson('/api/currency-rates/update')->assertStatus(403);
    }

    public function testIndexFiltersAndPaginates(): void
    {
        $this->useProviders([]);
        app(CurrencyService::class)->store([
            new CurrencyRateDto('2026-10-09', 'RUB', 'USD', 90),
            new CurrencyRateDto('2026-10-10', 'RUB', 'USD', 91),
            new CurrencyRateDto('2026-10-10', 'RUB', 'EUR', 100),
        ], 'test/provider');
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->actingAs($admin, 'api')->getJson('/api/currency-rates?limit=2')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.date', '2026-10-10')
            ->assertJsonPath('data.0.currency', 'EUR')
            ->assertJsonPath('data.0.source', 'test/provider')
            ->assertJsonPath('providers', []);

        $this->actingAs($admin, 'api')->getJson('/api/currency-rates?currency=usd&offset=1')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', '2026-10-09');

        $this->actingAs($admin, 'api')->getJson('/api/currency-rates?date=2026-10-10&currency=RUB')
            ->assertOk()
            ->assertJsonPath('total', 2);

        $this->actingAs($admin, 'api')->getJson('/api/currency-rates?date=10.10.2026')->assertStatus(422);
    }

    public function testUpdateRequestsProviders(): void
    {
        $provider = new class extends BasePlugin implements CurrencyRatesProviderInterface {
            public function currencyRates(DateTimeInterface $date): array
            {
                return [
                    new CurrencyRateDto($date->format('Y-m-d'), 'RUB', 'USD', 95),
                    new CurrencyRateDto($date->format('Y-m-d'), 'RUB', 'EUR', 105),
                ];
            }
        };
        $this->useProviders([$this->pluginModel('test/provider', 'Test rates', $provider)]);
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->actingAs($admin, 'api')->postJson('/api/currency-rates/update', ['date' => '2026-10-08'])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('providers.0.package', 'test/provider')
            ->assertJsonPath('providers.0.title', 'Test rates');

        $this->assertSame(2, CurrencyRate::where('date', '2026-10-08')->where('source', 'test/provider')->count());
    }

    /**
     * @param Plugin[] $plugins
     */
    private function useProviders(array $plugins): void
    {
        $service = $this->createMock(PluginService::class);
        $service->method('enabled')->willReturn(new Collection($plugins));
        $this->app->instance('currencyService', new CurrencyService($service));
    }

    private function pluginModel(string $package, string $title, PluginInterface $instance): Plugin
    {
        $model = new class extends Plugin {
            public ?PluginInterface $instance = null;

            public function getPlugin(): ?PluginInterface
            {
                return $this->instance;
            }
        };
        $model->package = $package;
        $model->title = $title;
        $model->instance = $instance;
        return $model;
    }

    private function createUser(int $role): User
    {
        $user = User::create([
            'name' => 'Currency Tester',
            'email' => 'currency_test_'.Str::random(6).'@ohmyshares.com',
            'password' => bcrypt('test_password'),
            'locale' => 'en',
        ]);
        $user->role = $role;
        $user->save();
        return $user;
    }
}
