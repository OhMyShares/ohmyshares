<?php

namespace Tests\Unit;

use App\Models\Ticker;
use App\Services\TickerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use OhMyShares\Plugin\Enums\TickerType;
use Tests\TestCase;

class TickerServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test of getting quotes from external services
     * @return void
     */
    public function testQuote()
    {
        /** @var TickerService $tickerService */
        $tickerService = App::make('tickerService');
        foreach (['LNZL', 'TSLA', 'SBER'] as $str) {
            Ticker::query()->where('ticker', 'like', $str.'%')->delete();
            Ticker::create([
                'ticker' => $str,
                'exchange' => '',
                'name' => $str,
                'type' => TickerType::Stock->value,
                'currency_code' => 'USD',
            ]);
            $quote = $tickerService->quote($str);
            $this->assertArrayHasKey('symbol', $quote);
            $this->assertContains($str, $quote);
            $this->assertArrayHasKey('price', $quote);
        }
    }

    /**
     * Tickers searching test
     */
    public function testSearch()
    {
        /** @var TickerService $tickerService */
        $tickerService = App::make('tickerService');
        $search = $tickerService->search('ap'); //dump($search);
        $this->assertIsArray($search);
        $search = $tickerService->search('apple'); //dump($search);
        $this->assertIsArray($search);
        $search = $tickerService->search('золото'); //dump($search);
        $this->assertIsArray($search);
        $search = $tickerService->search('XS1760786340'); //dump($search);
        $this->assertIsArray($search);
        $search = $tickerService->search('sb'); //dump($search);
        $this->assertIsArray($search);
    }
}
