<?php

namespace Tests\Integration;

use App\Exceptions\ImportException;
use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;
use App\Services\ReportImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OhMyShares\Plugin\Dtos\ImportResult;
use OhMyShares\Plugin\Dtos\Trade as TradeDto;
use OhMyShares\Plugin\Enums\AssetType;
use OhMyShares\Plugin\Enums\TradeType;
use Tests\TestCase;

class ReportImportServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ReportImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReportImportService::class);
    }

    public function testSaveCreatesTradesAndSkipsDuplicates(): void
    {
        $portfolio = $this->makePortfolio();
        $result = $this->makeResult();
        $result->notices[] = 'parser notice';

        $notices = $this->service->save($portfolio, $result);
        $this->assertSame(['parser notice'], $notices);

        $trades = Trade::where('portfolio_id', $portfolio->id)->whereNotNull('txn')->orderBy('id')->get();
        $this->assertCount(4, $trades);

        [$deposit, $buy, $rubOut, $usdIn] = $trades;
        $this->assertEquals(Trade::TYPE_CREDIT, $deposit->type);
        $this->assertSame(Trade::TICKER_MONEY, $deposit->ticker);
        $this->assertEquals(10000, $deposit->sum);
        $this->assertSame('RUB', $deposit->currency_code);

        $this->assertEquals(Trade::TYPE_BUY, $buy->type);
        $this->assertSame('ALRS', $buy->ticker);
        $this->assertEquals(6000, $buy->sum);
        $this->assertSame($portfolio->id.':t:1', $buy->txn);
        $this->assertSame(strtotime('2023-01-13 17:47:32'), $buy->created_at->getTimestamp());

        $this->assertEquals(Trade::TYPE_DEBIT, $rubOut->type);
        $this->assertEquals(-700, $rubOut->sum);
        $this->assertEquals(Trade::TYPE_CREDIT, $usdIn->type);
        $this->assertSame('USD', $usdIn->currency_code);
        $this->assertEquals($rubOut->id, $usdIn->related_trade_id);

        $notices = $this->service->save($portfolio, $this->makeResult());
        $this->assertSame([__('parsers.duplicates_skipped', ['count' => 4])], $notices);
        $this->assertSame(4, Trade::where('portfolio_id', $portfolio->id)->whereNotNull('txn')->count());

        $other = $this->makePortfolio();
        $this->service->save($other, $this->makeResult());
        $this->assertSame(4, Trade::where('portfolio_id', $other->id)->whereNotNull('txn')->count());
    }

    public function testFailedRowRollsBackWholeImport(): void
    {
        $portfolio = $this->makePortfolio();
        $result = $this->makeResult();
        $broken = $this->dto(TradeType::Buy, 'BROKEN', 1, 'RUB', '2023-01-20 10:00:00', 't:broken');
        $broken->notes = null;
        $broken->cost = 1;
        $broken->shares = 1;
        $broken->assetType = AssetType::Shares;
        $broken->ticker = str_repeat('X', 1000);
        $result->trades[] = $broken;

        try {
            $this->service->save($portfolio, $result);
            $this->fail('ImportException expected');
        } catch (ImportException $e) {
            $this->assertSame(__('parsers.trade_import_error', ['row' => 5]), $e->getMessage());
        }
        $this->assertSame(0, Trade::where('portfolio_id', $portfolio->id)->whereNotNull('txn')->count());
    }

    private function makeResult(): ImportResult
    {
        $result = new ImportResult;
        $result->trades[] = $this->dto(TradeType::Credit, TradeDto::TICKER_MONEY, 10000, 'RUB', '2023-01-10 00:00:00', 'm#1');

        $buy = $this->dto(TradeType::Buy, 'ALRS', 6000, 'RUB', '2023-01-13 17:47:32', 't:1');
        $buy->assetType = AssetType::Shares;
        $buy->cost = 60;
        $buy->shares = 100;
        $result->trades[] = $buy;

        $result->trades[] = $this->dto(TradeType::Debit, TradeDto::TICKER_MONEY, 700, 'RUB', '2023-01-14 12:00:00', 't:2');
        $usd = $this->dto(TradeType::Credit, TradeDto::TICKER_MONEY, 10, 'USD', '2023-01-14 12:00:00', 't:2:USD');
        $usd->relatedTxn = 't:2';
        $result->trades[] = $usd;
        return $result;
    }

    private function dto(TradeType $type, string $ticker, float $sum, string $currency, string $date, string $txn): TradeDto
    {
        $dto = new TradeDto;
        $dto->type = $type;
        $dto->ticker = $ticker;
        $dto->assetType = AssetType::Money;
        $dto->sum = $sum;
        $dto->currency = $currency;
        $dto->timestamp = strtotime($date);
        $dto->notes = 'import test';
        $dto->txn = $txn;
        return $dto;
    }

    private function makePortfolio(): Portfolio
    {
        $user = User::create([
            'name' => 'Import Test',
            'email' => uniqid('import_test_', true).'@example.com',
            'password' => bcrypt('password'),
            'locale' => 'en',
            'is_ignore_funds_sufficiency' => true,
        ]);
        return $user->portfolios->first();
    }
}
