<?php

namespace App\Analytics;

use App\Components\ArrayUtils;
use App\Models\Portfolio;
use App\Models\Trade;
use App\Services\CurrencyService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OhMyShares\Plugin\Enums\AssetType;

class Analytics
{
    protected $_content;

    /**
     * Analytics constructor.
     * @param iterable|null $content portfolio contents
     */
    public function __construct(protected Portfolio $_portfolio, iterable|null $content = null)
    {
        if (!$content) {
            $content = $this->_portfolio->content();
        }
        $this->setContent($content);
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content): self
    {
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Portfolio profit & loss by years
     */
    public function byProfitByYears(): array
    {
        // $row = Trade::select([
        //     //DB::raw('ROUND(AVG(asset)) as asset'),
        //     DB::raw('ROUND(AVG(currency_id)) as currency_id'),
        //     //DB::raw('SUM(shares) as count'),
        //     DB::raw('SUM(sum) as sum'),
        //     //DB::raw('COUNT(id) as trades'),
        // ])
        //     ->whereNotIn('type', [Trade::TYPE_DIVIDEND, Trade::TYPE_COUPON])
        //     ->where('portfolio_id', $this->_portfolio->id)
        //     ->groupBy('currency')
        //     ->get();

        //$content = $this->_content;
        $years = $this->_portfolio->availableYears();
        // $data = [];
        // foreach ($content as $key => $item) {
        //     $data[] = $item;
        // }
        return $years;
    }

    /**
     * Portfolio breakdown by asset types
     */
    public function byAssetTypes(): array
    {
        $content = $this->_content;
        $data = [];
        foreach ($content as $key => $item) {
            $type_key = $item->asset.';'.$item->currency_code;
            $data[$type_key]['label'] = __($item->assetLabel);
            $data[$type_key]['currency_sign'] = $item->currencySign;
            $current_cost = Arr::get($content[$key], 'current_cost', 0);
            $part_percent = Arr::get($content[$key], 'part_percent', 0);
            if (isset($data[$type_key]['total_cost'])) {
                $data[$type_key]['total_cost'] += $current_cost;
            } else {
                $data[$type_key]['total_cost'] = $current_cost;
            }
            if (isset($data[$type_key]['part_percent'])) {
                $data[$type_key]['part_percent'] += $part_percent;
            } else {
                $data[$type_key]['part_percent'] = $part_percent;
            }
        }
        return array_values($data);
    }

    /**
     * Portfolio breakdown by industry sectors
     */
    public function bySectors(): array
    {
        $content = $this->_content;
        $data = [];
        foreach ($content as $key => $item) {
            $type_key = $item->asset.';'.$item->currency_code;
            $sectorLabel = 'Other';
            if ($item->asset == AssetType::Money->value) {
                $sectorLabel = 'Money';
            } else {
                $ti = $item->tickerInfo();
                if ($ti) {
                    $sector = Cache::remember('sector_' . $ti->sector_id, 60, fn() => $ti->sector);
                    if ($sector) {
                        $sectorLabel = $sector->title;
                        $type_key .= ';'.$ti->sector_id;
                    }
                }
            }

            $data[$type_key]['label'] = __($sectorLabel);
            $data[$type_key]['currency_sign'] = $item->currencySign;

            $current_cost = Arr::get($content[$key], 'current_cost', 0);
            $part_percent = Arr::get($content[$key], 'part_percent', 0);
            if (isset($data[$type_key]['total_cost'])) {
                $data[$type_key]['total_cost'] += $current_cost;
            } else {
                $data[$type_key]['total_cost'] = $current_cost;
            }
            if (isset($data[$type_key]['part_percent'])) {
                $data[$type_key]['part_percent'] += $part_percent;
            } else {
                $data[$type_key]['part_percent'] = $part_percent;
            }

        }
        return array_values($data);
    }


    public function byEarnings(): array
    {
        $content = $this->_content;
        $data = [];
        foreach ($content as $key => $item)
        {
            $type_key = $item->asset.';'.$item->currency_code;
            if (!in_array($item->asset, [AssetType::Shares->value, AssetType::Bonds->value])) {
                continue;
            }

            if ($item->earnings <= 0) {
                continue;
            }

            $sectorLabel = '';
            $ti = $item->tickerInfo();
            if ($ti) {
                $sectorLabel = $ti->nameShort;
                $type_key .= ';'.$sectorLabel;
            }

            $data[$type_key]['label'] = __($sectorLabel);
            $data[$type_key]['currency_sign'] = $item->currencySign;
            //$data[$type_key]['currency_sign_native'] = $item->currencySign;

            //$part_percent = Arr::get($content[$key], 'part_percent', 0);
            ArrayUtils::addToItem($data[$type_key], 'earnings', $item->earnings);
            ArrayUtils::addToItem($data[$type_key], 'earnings_native', $item->earnings_native);
            //ArrayUtils::addToItem($data[$type_key], 'part_percent', $divs);
        }
        return array_values($data);
    }

    public function totalEarnings(): int|float
    {
        $sum = 0;
        foreach ($this->_content as $key => $item) {
            $sum += $item->earnings;
        }
        return $sum;
    }

    //public function totalTaxes()
    //{
    //    $sum = 0;
    //    foreach ($this->_content as $key => $item) {
    //        $sum += $item->paid_taxes_converted;
    //    }
    //    return $sum;
    //}
    //
    //public function totalFees()
    //{
    //    $sum = 0;
    //    foreach ($this->_content as $key => $item) {
    //        $sum += $item->paid_fees_converted;
    //    }
    //    return $sum;
    //}

    /**
     * Portfolio growth by months
     * @return array
     */
    public function growth()
    {
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        return Cache::remember('portfolio_'.$this->_portfolio->id.'_growth', 60*60, function () use ($currencyService): array {
            $data = [];
            $cumulative = 0;
            $items = DB::table('trades')
                ->select([
                    DB::raw("FROM_UNIXTIME(created_at, '%Y-%m') as month"),
                    DB::raw('SUM(sum) as total'),
                    'currency_code',
                ])
                ->where('portfolio_id', $this->_portfolio->id)
                ->groupBy(['month', 'currency_code'])
                ->orderBy('month')
                ->get();
            foreach ($items as $item) {
                $currency_code = $item->currency_code;
                $month = $item->month;
                $total = $item->total;
                if ($currency_code != $this->_portfolio->currency_code) {
                    $total = round($currencyService
                        ->convert($total, $currency_code, $this->_portfolio->currency_code), 2);
                }

                $cumulative += $total;

                if (isset($data[$month])) {
                    $data[$month]['total'] += $total;
                    $data[$month]['cumulative'] += $total;
                } else {
                    $data[$month] = [
                        'month' => $month,
                        'total' => round($total, 2),
                        'cumulative' => $cumulative,
                    ];
                }
            }
            return array_values($data);
        });
    }

}
