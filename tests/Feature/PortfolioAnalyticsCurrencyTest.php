<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OhMyShares\Plugin\Dtos\CurrencyRate as CurrencyRateDto;
use Tests\TestCase;

class PortfolioAnalyticsCurrencyTest extends TestCase
{
    use DatabaseTransactions;

    public function testAnalyticsCanBeExpressedInAnotherCurrencyWithoutChangingThePortfolio(): void
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);
        $currencyService->store([
            new CurrencyRateDto('2026-10-09', 'EUR', 'USD', 1.1),
        ]);

        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $date = strtotime('2024-06-15 12:00:00');
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, $date, sum: 10000);
        $this->addTrade($portfolio, Trade::TYPE_BUY, 'ZZTESTA', $date, 100, 10);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/portfolios/'.$portfolio->uid.'/analytics?currency=EUR');

        $response->assertStatus(200);
        $response->assertJsonPath('display_currency.code', 'EUR');
        $response->assertJsonPath('display_currency.sign', '€');
        $response->assertJsonPath('portfolio.currency_code', 'USD');

        $summary = $response->json('analytics.summary');
        $this->assertEqualsWithDelta(11000, $summary['total_cost'], 1);

        $this->assertSame(
            'USD',
            $portfolio->fresh()->currency_code,
            'Requesting analytics in another currency must not change the portfolio'
        );
    }

    public function testAnalyticsFallsBackToPortfolioCurrencyWhenNotConvertible(): void
    {
        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, strtotime('2024-06-15'), sum: 100);

        $this->actingAs($user, 'api')
            ->getJson('/api/portfolios/'.$portfolio->uid.'/analytics?currency=SYP')
            ->assertStatus(200)
            ->assertJsonPath('display_currency.code', 'USD')
            ->assertJsonPath('portfolio.currency_code', 'USD');

        $this->actingAs($user, 'api')
            ->getJson('/api/portfolios/'.$portfolio->uid.'/analytics?currency=NOPE')
            ->assertStatus(200)
            ->assertJsonPath('display_currency.code', 'USD');
    }

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Analytics Currency Test',
            'email' => uniqid('analytics_currency_test_', true).'@example.com',
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
