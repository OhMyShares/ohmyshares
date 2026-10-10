<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PortfolioItemCalcTest extends TestCase
{
    use DatabaseTransactions;

    protected function makePortfolio(): Portfolio
    {
        $user = User::create([
            'name' => 'Calc Test',
            'email' => uniqid('calc_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
        ]);
        return $user->portfolios()->first();
    }

    protected function addTrade(Portfolio $portfolio, array $attrs, bool $woMoney = true): Trade
    {
        $trade = new Trade($attrs);
        $trade->portfolio_id = $portfolio->id;
        $trade->user_id = $portfolio->user_id;
        $trade->is_wo_money = $woMoney;
        $trade->save();
        return $trade;
    }

    protected function findItem(Portfolio $portfolio, string $ticker): ?PortfolioItem
    {
        return PortfolioItem::where('portfolio_id', $portfolio->id)
            ->where('ticker', $ticker)
            ->where('currency_code', 'USD')
            ->first();
    }

    public function testFindCurrencyCodeAliases()
    {
        $this->assertEquals('USD', Currency::findCode('Доллар США'));
        $this->assertEquals('USD', Currency::findCode('доллар'));
        $this->assertEquals('USD', Currency::findCode('$'));
        $this->assertEquals('RUB', Currency::findCode('руб.'));
        $this->assertEquals('EUR', Currency::findCode('EUR'));
        $this->assertEquals('EUR', Currency::findCode('eur'));
        $this->assertNull(Currency::findCode('XXX'));
        $this->assertEquals('$', Currency::USD->sign());
        $this->assertEquals(Currency::UNIVERSAL_SIGN, Currency::DJF->sign());
        $this->assertEquals('US Dollar', Currency::USD->title());
        $this->assertCount(count(Currency::codes()), Currency::list());
    }

    public function testWeightedAveragePriceAfterPartialSell()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'MSFT', 'shares' => 10, 'cost' => 100,
        ]);
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_SELL, 'ticker' => 'MSFT', 'shares' => 5, 'cost' => 150,
        ]);

        $item = $this->findItem($portfolio, 'MSFT');
        $this->assertEquals(5, $item->count);
        $this->assertEquals(250, $item->sum);
        $this->assertEquals(100, $item->average_price);
        $this->assertEquals(250, $item->realized_gain);
    }

    public function testFeesIncludedInCostBasis()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'AAPL', 'shares' => 10, 'cost' => 100,
            'fee' => 5, 'fee_currency_code' => 'USD',
        ]);

        $item = $this->findItem($portfolio, 'AAPL');
        $this->assertEquals(1005, $item->sum);
        $this->assertEquals(100.5, $item->average_price);
        $this->assertEquals(5, $item->paid_fees);

        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_SELL, 'ticker' => 'AAPL', 'shares' => 10, 'cost' => 110,
            'fee' => 5, 'fee_currency_code' => 'USD',
        ]);

        $item->refresh();
        $this->assertEquals(0, $item->count);
        $this->assertEquals(-90, $item->sum);
        $this->assertEquals(90, $item->realized_gain);
        $this->assertEquals(10, $item->paid_fees);
        $this->assertEquals(0, $item->average_price);
    }

    public function testFeeInAnotherCurrencyExcludedFromBasis()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'AAPL', 'shares' => 10, 'cost' => 100,
            'fee' => 350, 'fee_currency_code' => 'RUB',
        ]);

        $item = $this->findItem($portfolio, 'AAPL');
        $this->assertEquals(1000, $item->sum);
        $this->assertEquals(100, $item->average_price);
        $this->assertEquals(0, $item->paid_fees);
    }

    public function testFullCloseKeepsRealizedGain()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'SBER', 'shares' => 5, 'cost' => 100,
        ]);
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_SELL, 'ticker' => 'SBER', 'shares' => 5, 'cost' => 120,
        ]);

        $item = $this->findItem($portfolio, 'SBER');
        $this->assertEquals(0, $item->count);
        $this->assertEquals(0, $item->average_price);
        $this->assertEquals(-100, $item->sum);
        $this->assertEquals(100, $item->realized_gain);
    }

    public function testShortPositionAccounting()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_SELL, 'ticker' => 'TSLA', 'shares' => 5, 'cost' => 150,
        ]);

        $item = $this->findItem($portfolio, 'TSLA');
        $this->assertEquals(-5, $item->count);
        $this->assertEquals(-750, $item->sum);
        $this->assertEquals(150, $item->average_price);
        $this->assertEquals(0, $item->realized_gain);

        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'TSLA', 'shares' => 5, 'cost' => 100,
        ]);

        $item->refresh();
        $this->assertEquals(0, $item->count);
        $this->assertEquals(0, $item->average_price);
        $this->assertEquals(-250, $item->sum);
        $this->assertEquals(250, $item->realized_gain);
    }

    public function testDividendsDoNotAffectCostBasis()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'MSFT', 'shares' => 10, 'cost' => 100,
        ]);
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_DIVIDEND, 'ticker' => 'MSFT', 'sum' => 30,
        ]);

        $item = $this->findItem($portfolio, 'MSFT');
        $this->assertEquals(10, $item->count);
        $this->assertEquals(1000, $item->sum);
        $this->assertEquals(100, $item->average_price);
        $this->assertEquals(30, $item->earned_dividends);
    }

    public function testMoneyItemSumFromMoneyOperations()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, ['type' => Trade::TYPE_CREDIT, 'sum' => 500]);
        $this->addTrade($portfolio, ['type' => Trade::TYPE_DEBIT, 'sum' => 200]);
        $this->addTrade($portfolio, ['type' => Trade::TYPE_FEE, 'sum' => 10]);

        $item = $this->findItem($portfolio, Trade::TICKER_MONEY);
        $this->assertEquals(290, $item->sum);
        $this->assertEquals(10, $item->paid_fees);
        $this->assertEquals(0, $item->count);
    }

    public function testPortfolioCalcCommandWithUserOption()
    {
        $portfolio = $this->makePortfolio();
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'MSFT', 'shares' => 10, 'cost' => 100,
        ]);

        $this->artisan('portfolio:calc', [
            '--user' => $portfolio->user_id,
            '--force' => true,
        ])->assertExitCode(0);

        $this->artisan('portfolio:calc', [
            '--user' => 999999999,
        ])->assertExitCode(1);
    }

    public function testBuyWithoutMoneyThrowsException()
    {
        $portfolio = $this->makePortfolio();
        $this->expectException(HttpException::class);
        $this->addTrade($portfolio, [
            'type' => Trade::TYPE_BUY, 'ticker' => 'MSFT', 'shares' => 10, 'cost' => 100,
        ], false);
    }

    public function testSellDoesNotRequireMoney()
    {
        $portfolio = $this->makePortfolio();
        $trade = $this->addTrade($portfolio, [
            'type' => Trade::TYPE_SELL, 'ticker' => 'MSFT', 'shares' => 5, 'cost' => 150,
        ], false);

        $this->assertTrue($trade->exists);
        $item = $this->findItem($portfolio, 'MSFT');
        $this->assertEquals(-5, $item->count);

        $money = $this->findItem($portfolio, Trade::TICKER_MONEY);
        $this->assertEquals(750, $money->sum);
    }
}
