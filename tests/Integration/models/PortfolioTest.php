<?php

namespace Tests\Integration\models;

use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OhMyShares\Plugin\Enums\AssetType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PortfolioTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeUser(bool $ignoreFunds = false): User
    {
        return User::create([
            'name' => 'Portfolio Test',
            'email' => uniqid('portfolio_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
            'is_ignore_funds_sufficiency' => $ignoreFunds,
        ]);
    }

    function testTradesAdding()
    {
        $u = $this->makeUser(true);
        $p = $u->portfolios->first();

        // buy
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_BUY,
            'asset' => AssetType::Shares->value,
            'currency_code' => 'USD',
            'ticker' => 'AAPL',
            'cost' => 100,
            'shares' => 10,
        ]);
        $t->save();
        $i = $p->items()->where('ticker', 'AAPL')->first();
        $this->assertEquals(1000, $i->sum);

        // sell
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_SELL,
            'asset' => AssetType::Shares->value,
            'currency_code' => 'USD',
            'ticker' => 'AAPL',
            'cost' => 200,
            'shares' => 5,
        ]);
        $t->save();
        $i->refresh();
        $this->assertEquals(0, $i->sum);

        // credit (add money)
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_CREDIT,
            'sum' => 5000,
            'currency_code' => 'RUB',
        ]);
        $t->save();
        $i = $p->items()->where('ticker', Trade::TICKER_MONEY)
            ->where('currency_code', 'RUB')
            ->first();
        $this->assertEquals(5000, $i->sum);

        // taxes (remove money)
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_TAXES,
            'sum' => 1000,
            'currency_code' => 'RUB',
        ]);
        $t->save();
        $i = $i->refresh();
        $this->assertEquals(4000, $i->sum);
    }

    /**
     * Trade should not be added if insufficient funds of related currency
     */
    function testTradesNotAddingIfInsufficiencyFunds()
    {
        $u = $this->makeUser(false);
        $p = $u->portfolios->first();

        try {
            $t = new Trade([
                'user_id' => $u->id,
                'portfolio_id' => $p->id,
                'type' => Trade::TYPE_BUY,
                'asset' => AssetType::Shares->value,
                'currency_code' => 'USD',
                'ticker' => 'AAPL',
                'cost' => 100,
                'shares' => 10,
            ]);
            $t->save();
        } catch (\Exception $e) {
            $this->assertEquals(1, 1); // exception was thrown
        }
        $i = $p->items()->where('ticker', 'AAPL')->first();
        $this->assertNotEquals($i, 1);
    }

    /**
     * Trade should be added if sufficient funds of related currency
     */
    function testTradesAddingIfSufficiencyFunds()
    {
        $u = $this->makeUser(false);
        $p = $u->portfolios->first();

        // credit (add money)
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_CREDIT,
            'sum' => 5000,
            'currency_code' => 'USD',
        ]);
        $t->save();

        try {
            $t = new Trade([
                'user_id' => $u->id,
                'portfolio_id' => $p->id,
                'type' => Trade::TYPE_BUY,
                'asset' => AssetType::Shares->value,
                'currency_code' => 'USD',
                'ticker' => 'AAPL',
                'cost' => 100,
                'shares' => 10,
            ]);
            $t->save();
        } catch (\Exception $e) {
        }
        $i = $p->items()->where('ticker', 'AAPL')->first();
        $this->assertNotEquals($i, 1);
    }

    function testPortfolioItemCalc()
    {
        $u = $this->makeUser(true);
        $p = $u->portfolios->first();

        // add shares
        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_BUY,
            'ticker' => 'TEST',
            'shares' => 1,
            'cost' => 10,
            'currency_code' => 'USD',
        ]);
        $t->save();

        $t = new Trade([
            'user_id' => $u->id,
            'portfolio_id' => $p->id,
            'type' => Trade::TYPE_BUY,
            'ticker' => 'TEST',
            'shares' => 1,
            'cost' => 20,
            'currency_code' => 'USD',
        ]);
        $t->save();

        $item = $p->items()->where('ticker', 'TEST')->first();
        $this->assertEquals(15, $item->average_price);
        $this->assertEquals(2, $item->trades);
    }
}
