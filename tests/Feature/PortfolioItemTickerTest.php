<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PortfolioItemTickerTest extends TestCase
{
    use DatabaseTransactions;

    private const OLD_TICKER = 'US0970231058';

    public function testChangeTickerReplacesTickerInAllPositionTrades(): void
    {
        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, 'USD', sum: 10000);
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, 'EUR', sum: 10000);
        $first = $this->addTrade($portfolio, Trade::TYPE_BUY, self::OLD_TICKER, 'USD', 100, 2);
        $second = $this->addTrade($portfolio, Trade::TYPE_BUY, self::OLD_TICKER, 'USD', 120, 3);
        $otherCurrency = $this->addTrade($portfolio, Trade::TYPE_BUY, self::OLD_TICKER, 'EUR', 90, 1);
        $otherTicker = $this->addTrade($portfolio, Trade::TYPE_BUY, 'AAPL', 'USD', 150, 1);
        $item = $this->findItem($portfolio, self::OLD_TICKER, 'USD');

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/portfolio-items/'.$item->uid.'/ticker', ['ticker' => 'ba']);

        $response->assertStatus(200)->assertJsonPath('ticker', 'BA');
        $this->assertEquals(5, $response->json('count'));
        $this->assertSame('BA', $first->fresh()->ticker);
        $this->assertSame('BA', $second->fresh()->ticker);
        $this->assertSame(self::OLD_TICKER, $otherCurrency->fresh()->ticker);
        $this->assertSame('AAPL', $otherTicker->fresh()->ticker);
        $this->assertNull(PortfolioItem::find($item->id));
        $this->assertNotNull($this->findItem($portfolio, self::OLD_TICKER, 'EUR'));
        $newItem = $this->findItem($portfolio, 'BA', 'USD');
        $this->assertNotNull($newItem);
        $this->assertSame($newItem->uid, $response->json('uid'));
        $this->assertSame(2, Trade::whereIn('related_trade_id', [$first->id, $second->id])->count());
    }

    public function testChangeTickerMergesIntoExistingPosition(): void
    {
        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, 'USD', sum: 10000);
        $this->addTrade($portfolio, Trade::TYPE_BUY, self::OLD_TICKER, 'USD', 100, 2);
        $this->addTrade($portfolio, Trade::TYPE_BUY, 'BA', 'USD', 100, 1);
        $item = $this->findItem($portfolio, self::OLD_TICKER, 'USD');
        $existing = $this->findItem($portfolio, 'BA', 'USD');

        $response = $this->actingAs($user, 'api')
            ->patchJson('/api/portfolio-items/'.$item->uid.'/ticker', ['ticker' => 'BA']);

        $response->assertStatus(200)->assertJsonPath('uid', $existing->uid);
        $this->assertEquals(3, $response->json('count'));
    }

    public function testChangeTickerValidation(): void
    {
        $user = $this->makeUser();
        $portfolio = $user->portfolios->first();
        $this->addTrade($portfolio, Trade::TYPE_CREDIT, Trade::TICKER_MONEY, 'USD', sum: 10000);
        $this->addTrade($portfolio, Trade::TYPE_BUY, self::OLD_TICKER, 'USD', 100, 2);
        $item = $this->findItem($portfolio, self::OLD_TICKER, 'USD');
        $money = $this->findItem($portfolio, Trade::TICKER_MONEY, 'USD');

        $this->actingAs($user, 'api')
            ->patchJson('/api/portfolio-items/'.$item->uid.'/ticker', [])
            ->assertStatus(422);
        $this->actingAs($user, 'api')
            ->patchJson('/api/portfolio-items/'.$item->uid.'/ticker', ['ticker' => Trade::TICKER_MONEY])
            ->assertStatus(422);
        $this->actingAs($user, 'api')
            ->patchJson('/api/portfolio-items/'.$money->uid.'/ticker', ['ticker' => 'BA'])
            ->assertStatus(422);
        $this->actingAs($this->makeUser(), 'api')
            ->patchJson('/api/portfolio-items/'.$item->uid.'/ticker', ['ticker' => 'BA'])
            ->assertStatus(404);
        $this->assertSame(1, Trade::where('portfolio_id', $portfolio->id)->where('ticker', self::OLD_TICKER)->count());
    }

    private function findItem(Portfolio $portfolio, string $ticker, string $currency): ?PortfolioItem
    {
        return PortfolioItem::where('portfolio_id', $portfolio->id)
            ->where('ticker', $ticker)
            ->where('currency_code', $currency)
            ->first();
    }

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Ticker Test',
            'email' => uniqid('ticker_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
            'is_ignore_funds_sufficiency' => true,
        ]);
    }

    private function addTrade(
        Portfolio $portfolio,
        int $type,
        string $ticker,
        string $currency,
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
            'currency_code' => $currency,
            'fee_currency_code' => $currency,
        ]);
        $trade->save();
        return $trade;
    }
}
