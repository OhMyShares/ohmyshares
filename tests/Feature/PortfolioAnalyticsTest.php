<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PortfolioAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    public function testAnalyticsBreakdownsAndSummary(): void
    {
        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $date = strtotime('2024-06-15 12:00:00');
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, $date, sum: 10000);
        $this->addTrade($portfolio, Trade::TYPE_BUY, 'ZZTESTA', $date, 100, 10);
        $this->addTrade($portfolio, Trade::TYPE_BUY, 'ZZTESTB', $date, 50, 4);
        $this->addTrade($portfolio, Trade::TYPE_SELL, 'ZZTESTB', $date, 60, 2);
        $this->addTrade($portfolio, Trade::TYPE_DIVIDEND, 'ZZTESTA', $date, sum: 30);
        $this->addTrade($portfolio, Trade::TYPE_FEE, Trade::TICKER_MONEY, $date, sum: 5);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/portfolios/'.$portfolio->uid.'/analytics');

        $response->assertStatus(200);
        $analytics = $response->json('analytics');

        $this->assertCount(2, $analytics['asset_types']);
        $this->assertSame('Money', $analytics['asset_types'][0]['label']);
        $this->assertEquals(8945, $analytics['asset_types'][0]['total_cost']);
        $this->assertEquals(1080, $analytics['asset_types'][1]['total_cost']);
        $this->assertEqualsWithDelta(100, array_sum(array_column($analytics['asset_types'], 'part_percent')), 0.05);

        $this->assertSame(['Money', 'Other'], array_column($analytics['sectors'], 'label'));
        $this->assertSame(['USD'], array_column($analytics['currencies'], 'label'));

        $this->assertCount(1, $analytics['earnings']);
        $this->assertSame('ZZTESTA', $analytics['earnings'][0]['label']);
        $this->assertEquals(30, $analytics['earnings'][0]['earnings']);

        $summary = $analytics['summary'];
        $this->assertEquals(10025, $summary['total_cost']);
        $this->assertEquals(8945, $summary['cash']);
        $this->assertEquals(20, $summary['realized_gain']);
        $this->assertEquals(-20, $summary['unrealized_gain']);
        $this->assertEquals(30, $summary['earnings']);
        $this->assertEquals(30, $summary['total_return']);
        $this->assertSame(2, $summary['positions_count']);
        $this->assertSame(0, $summary['profitable_count']);
        $this->assertSame(1, $summary['losing_count']);
        $this->assertEquals(92.59, $summary['largest_part_percent']);
        $this->assertEquals(100, $summary['top5_part_percent']);

        $this->assertSame([[
            'year' => 2024,
            'dividends' => 30,
            'coupons' => 0,
            'fees' => 5,
            'taxes' => 0,
        ]], array_map(
            fn(array $row): array => array_map(fn($value) => (int)$value, $row),
            $analytics['income_by_years']
        ));
    }

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Analytics Test',
            'email' => uniqid('analytics_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
            'is_ignore_funds_sufficiency' => true,
        ]);
    }

    private function addTrade(
        Portfolio $portfolio,
        int $type,
        string $ticker,
        int $created_at,
        ?float $cost = null,
        ?float $shares = null,
        ?float $sum = null,
    ): Trade {
        $trade = new Trade([
            'user_id' => $portfolio->user_id,
            'portfolio_id' => $portfolio->id,
            'type' => $type,
            'ticker' => $ticker,
            'cost' => $cost,
            'shares' => $shares,
            'sum' => $sum,
            'currency_code' => 'USD',
            'fee_currency_code' => 'USD',
            'created_at' => $created_at,
        ]);
        $trade->save();
        return $trade;
    }
}
