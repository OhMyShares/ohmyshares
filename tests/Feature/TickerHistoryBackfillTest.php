<?php

namespace Tests\Feature;

use App\Jobs\GetHistory;
use App\Models\History;
use App\Models\Ticker;
use App\Models\Trade;
use App\Models\User;
use App\Services\PluginService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use OhMyShares\Plugin\Dtos\Ticker as TickerDto;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Enums\TickerType;
use Tests\TestCase;

class TickerHistoryBackfillTest extends TestCase
{
    use DatabaseTransactions;

    private const TICKER = 'TSTBKFL';

    public function testRequestsHistoryFromFirstTradeDateWhenOlderHistoryMissing(): void
    {
        $ticker = $this->makeTicker();
        $this->addTrade(Carbon::create(2020, 3, 10, 12));
        $this->addHistory(Carbon::today()->subDays(3));

        $this->expectHistoryRequestFrom('2020-03-10');

        $this->assertTrue($ticker->updateHistory());
    }

    public function testRequestsHistoryFromLastDateWhenHistoryCoversTrades(): void
    {
        $ticker = $this->makeTicker();
        $this->addTrade(Carbon::create(2020, 3, 10, 12));
        $this->addHistory(Carbon::create(2020, 3, 9));
        $last = Carbon::today()->subDays(3);
        $this->addHistory($last);

        $this->expectHistoryRequestFrom($last->format('Y-m-d'));

        $this->assertTrue($ticker->updateHistory());
    }

    public function testHistoryEndpointDispatchesBackfillWhenOlderHistoryMissing(): void
    {
        $this->makeTicker();
        $user = $this->addTrade(Carbon::create(2020, 3, 10, 12));
        $this->addHistory(Carbon::today()->subDays(3));
        Queue::fake();

        $this->actingAs($user, 'api')
            ->getJson('/api/tickers/'.self::TICKER.'/history?begin=2020-02-10&end=2020-04-10')
            ->assertStatus(200);

        Queue::assertPushed(GetHistory::class, fn(GetHistory $job): bool => $job->ticker === self::TICKER);
    }

    public function testHistoryEndpointSkipsBackfillWhenHistoryCoversTrades(): void
    {
        $this->makeTicker();
        $user = $this->addTrade(Carbon::create(2020, 3, 10, 12));
        $this->addHistory(Carbon::create(2020, 3, 9));
        Queue::fake();

        $this->actingAs($user, 'api')
            ->getJson('/api/tickers/'.self::TICKER.'/history?begin=2020-02-10&end=2020-04-10')
            ->assertStatus(200)
            ->assertJsonCount(1);

        Queue::assertNotPushed(GetHistory::class);
    }

    public function testIsNotOutdatedWhenHistoryIsFromToday(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 29, 15));
        $ticker = $this->makeTicker();
        $this->addHistory(Carbon::create(2026, 9, 29));

        $this->assertFalse($ticker->isOutdated());
    }

    public function testIsNotOutdatedWhenHistoryIsFromPreviousDay(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 29, 15));
        $ticker = $this->makeTicker();
        $this->addHistory(Carbon::create(2026, 9, 28));

        $this->assertFalse($ticker->isOutdated());
    }

    public function testIsOutdatedWhenHistoryIsTwoDaysOld(): void
    {
        $this->travelTo(Carbon::create(2026, 9, 29, 15));
        $ticker = $this->makeTicker();
        $this->addHistory(Carbon::create(2026, 9, 27));

        $this->assertTrue($ticker->isOutdated());
    }

    private function expectHistoryRequestFrom(string $date): void
    {
        $this->mock(PluginService::class)
            ->shouldReceive('onHistoryRequest')
            ->once()
            ->withArgs(fn(TickerDto $dto, ?DateTimeInterface $start, ?DateTimeInterface $end): bool => $dto->ticker === self::TICKER && $start?->format('Y-m-d') === $date);
    }

    private function makeTicker(): Ticker
    {
        Cache::flush();
        return Ticker::create([
            'ticker' => self::TICKER,
            'exchange' => '',
            'name' => 'Backfill Test',
            'type' => TickerType::Stock->value,
            'currency_code' => 'USD',
        ]);
    }

    private function addTrade(Carbon $date): User
    {
        $user = User::create([
            'name' => 'Backfill Test',
            'email' => uniqid('backfill_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
            'is_ignore_funds_sufficiency' => true,
        ]);
        Trade::query()->insert([
            'user_id' => $user->id,
            'portfolio_id' => $user->portfolios->first()->id,
            'type' => Trade::TYPE_BUY,
            'asset' => AssetType::Shares->value,
            'ticker' => self::TICKER,
            'exchange' => '',
            'created_at' => $date->timestamp,
            'cost' => 100,
            'shares' => 1,
            'sum' => 100,
        ]);
        return $user;
    }

    private function addHistory(Carbon $date): void
    {
        History::query()->insert([
            'ticker' => self::TICKER,
            'exchange' => '',
            'asset' => AssetType::Shares->value,
            'date' => $date->format('Y-m-d'),
            'close' => 100,
        ]);
    }
}
