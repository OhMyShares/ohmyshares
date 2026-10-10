<?php

namespace Tests\Unit;

use App\Models\CurrencyRate;
use App\Models\Plugin;
use App\Services\CurrencyService;
use App\Services\PluginService;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OhMyShares\Plugin\BasePlugin;
use OhMyShares\Plugin\CurrencyRatesProviderInterface;
use OhMyShares\Plugin\Dtos\CurrencyRate as CurrencyRateDto;
use OhMyShares\Plugin\PluginInterface;
use RuntimeException;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        CurrencyRate::query()->delete();
        $this->service = new CurrencyService(new PluginService);
        $this->service->store([
            new CurrencyRateDto('2026-10-09', 'RUB', 'USD', 90),
            new CurrencyRateDto('2026-10-09', 'RUB', 'EUR', 100),
        ]);
    }

    public function testConvertsBetweenCurrenciesWithRates(): void
    {
        $this->assertEqualsWithDelta(90.0, $this->service->convert(100, 'USD', 'EUR'), 0.0001);
    }

    public function testConvertToCurrencyWithoutRateReturnsZero(): void
    {
        $this->assertSame(0.0, $this->service->convert(400, 'USD', 'SYP'));
    }

    public function testConvertsFromAndToBaseCurrency(): void
    {
        $this->assertEqualsWithDelta(9000.0, $this->service->convert(100, 'USD', 'RUB'), 0.0001);
        $this->assertEqualsWithDelta(1.0, $this->service->convert(90, 'rub', 'usd'), 0.0001);
        $this->assertSame(5.0, $this->service->convert('5', 'EUR', 'EUR'));
    }

    public function testUsesLatestRate(): void
    {
        $this->service->store([
            new CurrencyRateDto('2026-10-10', 'RUB', 'USD', 80),
            new CurrencyRateDto('2026-10-01', 'RUB', 'USD', 70),
        ]);
        $this->assertEqualsWithDelta(80.0, $this->service->rate('USD', 'RUB'), 0.0001);
    }

    public function testConvertsThroughRatesOfDifferentBases(): void
    {
        $this->service->store([
            new CurrencyRateDto('2026-10-09', 'EUR', 'BRL', 0.2),
        ]);
        $this->assertEqualsWithDelta(5.0, $this->service->convert(1, 'EUR', 'BRL'), 0.0001);
        $this->assertEqualsWithDelta(20.0, $this->service->convert(1, 'BRL', 'RUB'), 0.0001);
        $this->assertEqualsWithDelta(4.5, $this->service->convert(1, 'USD', 'BRL'), 0.0001);
        $this->assertEqualsCanonicalizing(['RUB', 'USD', 'EUR', 'BRL'], $this->service->currencies());
    }

    public function testStoreSkipsInvalidRates(): void
    {
        $stored = $this->service->store([
            new CurrencyRateDto('2026-10-09', 'RUB', 'RUB', 1),
            new CurrencyRateDto('2026-10-09', 'RUB', 'GBP', 0),
            new CurrencyRateDto('2026-10-09', 'RUB', 'G B', 110),
            new CurrencyRateDto('09.10.2026', 'RUB', 'GBP', 110),
            new CurrencyRateDto('2026-10-09', 'rub', 'gbp', 110),
        ]);
        $this->assertSame(1, $stored);
        $this->assertEqualsWithDelta(110.0, $this->service->rate('GBP', 'RUB'), 0.0001);
    }

    public function testUpdateStoresRatesOfProviderPlugins(): void
    {
        $provider = new class extends BasePlugin implements CurrencyRatesProviderInterface {
            public function currencyRates(DateTimeInterface $date): array
            {
                return [new CurrencyRateDto($date->format('Y-m-d'), 'RUB', 'USD', 95)];
            }
        };
        $failing = new class extends BasePlugin implements CurrencyRatesProviderInterface {
            public function currencyRates(DateTimeInterface $date): array
            {
                throw new RuntimeException('source is down');
            }
        };
        $plugins = $this->createMock(PluginService::class);
        $plugins->method('enabled')->willReturn(new Collection([
            $this->pluginModel('test/failing', $failing),
            $this->pluginModel('test/other', new BasePlugin),
            $this->pluginModel('test/provider', $provider),
        ]));
        $service = new CurrencyService($plugins);

        $this->assertSame(1, $service->update(new DateTimeImmutable('2026-10-10')));
        $this->assertEqualsWithDelta(95.0, $service->rate('USD', 'RUB'), 0.0001);
        $this->assertSame('test/provider', CurrencyRate::where('date', '2026-10-10')->value('source'));
    }

    private function pluginModel(string $package, PluginInterface $instance): Plugin
    {
        $model = new class extends Plugin {
            public ?PluginInterface $instance = null;

            public function getPlugin(): ?PluginInterface
            {
                return $this->instance;
            }
        };
        $model->package = $package;
        $model->instance = $instance;
        return $model;
    }
}
