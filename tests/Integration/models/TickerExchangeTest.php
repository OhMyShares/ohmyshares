<?php

namespace Tests\Integration\models;

use App\Models\History;
use App\Models\Plugin;
use App\Models\Ticker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OhMyShares\Plugin\Dtos\History as HistoryDto;
use OhMyShares\Plugin\Dtos\Ticker as TickerDto;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Enums\TickerType;
use Tests\TestCase;

class TickerExchangeTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTickerDto(string $ticker, string $exchange, string $isin): TickerDto
    {
        $dto = new TickerDto;
        $dto->ticker = $ticker;
        $dto->name = 'Test Corp';
        $dto->exchange = $exchange;
        $dto->isin = $isin;
        $dto->country = 'US';
        $dto->currency = 'USD';
        $dto->type = TickerType::Stock;
        return $dto;
    }

    private function addHistoryRow(string $ticker, string $exchange, string $date, float $close): History
    {
        $row = new History();
        $row->ticker = $ticker;
        $row->exchange = $exchange;
        $row->asset = AssetType::Shares->value;
        $row->date = $date;
        $row->close = $close;
        $row->save();
        return $row;
    }

    public function testAddTickerCreatesSeparateRowsPerExchange()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005'));
        $plugin->addTicker($this->makeTickerDto('AAPL', 'SPB', 'US0378331004'));

        $this->assertEquals(2, Ticker::where('ticker', 'AAPL')->count());
        $nasdaq = Ticker::where('ticker', 'AAPL')->where('exchange', 'NASDAQ')->first();
        $spb = Ticker::where('ticker', 'AAPL')->where('exchange', 'SPB')->first();
        $this->assertNotNull($nasdaq);
        $this->assertNotNull($spb);
        $this->assertEquals('US0378331005', $nasdaq->isin);
        $this->assertEquals('US0378331004', $spb->isin);
    }

    public function testAddTickerIsIdempotentPerExchange()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005'));
        $plugin->addTicker($this->makeTickerDto('AAPL', 'SPB', 'US0378331004'));

        $dto = $this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005');
        $dto->name = 'Apple Inc';
        $plugin->addTicker($dto);

        $this->assertEquals(2, Ticker::where('ticker', 'AAPL')->count());
        $this->assertEquals(
            'Apple Inc',
            Ticker::where('ticker', 'AAPL')->where('exchange', 'NASDAQ')->first()->name
        );
        $this->assertEquals(
            'Test Corp',
            Ticker::where('ticker', 'AAPL')->where('exchange', 'SPB')->first()->name
        );
    }

    public function testFindModelMatchesByTickerAndExchange()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005'));
        $plugin->addTicker($this->makeTickerDto('AAPL', 'SPB', 'US0378331004'));

        $this->assertEquals('NASDAQ', Ticker::findModel('AAPL', 'NASDAQ')->exchange);
        $this->assertEquals('SPB', Ticker::findModel('aapl', 'SPB')->exchange);
        $this->assertNull(Ticker::findModel('AAPL', 'MCX'));
        $this->assertNotNull(Ticker::findModel('AAPL'));
    }

    public function testFuzzySearchStillWorks()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005'));
        $plugin->addTicker($this->makeTickerDto('AAPL', 'SPB', 'US0378331004'));

        $this->assertEquals('AAPL', Ticker::findFuzzy('aap')->ticker);
        $this->assertEquals('AAPL', Ticker::findFuzzy('US037833')->ticker);
        $this->assertEquals(2, Ticker::getSuggestions('aap')->count());
        $this->assertEquals('AAPL', Ticker::findByIsin('US0378331004'));
    }

    public function testAddHistoryResolvesExchangeFromTicker()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('TEST', 'SPB', 'RU000TEST001'));

        $dto = new HistoryDto;
        $dto->ticker = 'TEST';
        $dto->date = '2026-09-01';
        $dto->assetType = AssetType::Shares;
        $dto->close = 10.5;
        $plugin->addHistory($dto);

        $row = History::where('ticker', 'TEST')->where('date', '2026-09-01')->first();
        $this->assertNotNull($row);
        $this->assertEquals('SPB', $row->exchange);

        $dto->close = 11.0;
        $plugin->addHistory($dto);
        $this->assertEquals(1, History::where('ticker', 'TEST')->count());
        $this->assertEquals(11.0, History::where('ticker', 'TEST')->first()->close);
    }

    public function testGetHistoryFiltersByExchange()
    {
        $this->addHistoryRow('AAPL', 'NASDAQ', '2026-09-01', 100);
        $this->addHistoryRow('AAPL', 'SPB', '2026-09-01', 200);

        $plugin = new Plugin;
        $all = $plugin->getHistory('AAPL', AssetType::Shares);
        $spb = $plugin->getHistory('AAPL', AssetType::Shares, null, null, 'SPB');

        $this->assertCount(2, $all);
        $this->assertCount(1, $spb);
        $this->assertEquals(200, $spb[0]->close);
    }

    public function testGetTickerWithoutExchangeReturnsAnyMatch()
    {
        $plugin = new Plugin;
        $plugin->addTicker($this->makeTickerDto('AAPL', 'NASDAQ', 'US0378331005'));
        $plugin->addTicker($this->makeTickerDto('AAPL', 'SPB', 'US0378331004'));

        $dto = $plugin->getTicker('AAPL');
        $this->assertNotNull($dto);
        $this->assertEquals('AAPL', $dto->ticker);
        $this->assertEquals('SPB', $plugin->getTicker('AAPL', 'SPB')->exchange);
        $this->assertNull($plugin->getTicker('AAPL', 'MCX'));
    }
}
