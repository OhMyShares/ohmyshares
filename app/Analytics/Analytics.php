<?php

namespace App\Analytics;

use App\Components\ArrayUtils;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
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
            $content = $this->_portfolio->items;
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
        return $this->groupByCost(fn(PortfolioItem $item): array => [
            (string)$item->asset,
            __($item->assetLabel),
        ]);
    }

    /**
     * Portfolio breakdown by industry sectors
     */
    public function bySectors(): array
    {
        return $this->groupByCost(function (PortfolioItem $item): array {
            if ($item->asset == AssetType::Money->value) {
                return ['money', __('Money')];
            }
            $ti = $item->tickerInfo();
            $sector = $ti && $ti->sector_id
                ? Cache::remember('sector_' . $ti->sector_id, 60, fn() => $ti->sector)
                : null;
            if ($sector) {
                return ['sector_' . $ti->sector_id, __($sector->title)];
            }
            return ['other', __('Other')];
        });
    }

    public function byCurrencies(): array
    {
        return $this->groupByCost(fn(PortfolioItem $item): array => [
            $item->currency_code,
            $item->currency_code,
        ]);
    }


    public function byEarnings(): array
    {
        $content = $this->_content;
        $data = [];
        foreach ($content as $key => $item)
        {
            $type_key = $item->ticker.';'.$item->currency_code;
            if (!in_array($item->asset, [AssetType::Shares->value, AssetType::Bonds->value])) {
                continue;
            }

            if ($item->earnings <= 0) {
                continue;
            }

            $data[$type_key]['label'] = $item->tickerInfo()?->nameShort ?: $item->ticker;
            $data[$type_key]['currency_sign'] = $item->currencySign;
            //$data[$type_key]['currency_sign_native'] = $item->currencySign;

            //$part_percent = Arr::get($content[$key], 'part_percent', 0);
            ArrayUtils::addToItem($data[$type_key], 'earnings', $item->earnings);
            ArrayUtils::addToItem($data[$type_key], 'earnings_native', $item->earnings_native);
            //ArrayUtils::addToItem($data[$type_key], 'part_percent', $divs);
        }
        usort($data, fn(array $a, array $b): int => $b['earnings'] <=> $a['earnings']);
        return $data;
    }

    public function totalEarnings(): int|float
    {
        $sum = 0;
        foreach ($this->_content as $key => $item) {
            $sum += $item->earnings;
        }
        return $sum;
    }

    public function summary(): array
    {
        $summary = [
            'total_cost' => 0,
            'cash' => 0,
            'cost_basis' => 0,
            'total_gain' => 0,
            'unrealized_gain' => 0,
            'unrealized_gain_percent' => 0,
            'realized_gain' => 0,
            'earnings' => 0,
            'total_return' => 0,
            'positions_count' => 0,
            'profitable_count' => 0,
            'losing_count' => 0,
            'largest_part_percent' => 0,
            'top5_part_percent' => 0,
        ];
        $open_costs = [];
        foreach ($this->_content as $item) {
            $summary['total_cost'] += $item->current_cost;
            $summary['earnings'] += $item->earnings;
            if ($item->asset == AssetType::Money->value) {
                $summary['cash'] += $item->current_cost;
                continue;
            }
            $realized = $this->toPortfolioCurrency((float)$item->realized_gain, $item->currency_code);
            $summary['total_gain'] += $item->current_gain;
            $summary['realized_gain'] += $realized;
            if ($item->count == 0) {
                continue;
            }
            $unrealized = $item->current_gain - $realized;
            $summary['positions_count']++;
            $summary['unrealized_gain'] += $unrealized;
            $summary['cost_basis'] += $item->current_cost - $unrealized;
            if ($unrealized > 0) {
                $summary['profitable_count']++;
            } elseif ($unrealized < 0) {
                $summary['losing_count']++;
            }
            if ($item->current_cost > 0) {
                $open_costs[] = $item->current_cost;
            }
        }
        $summary['total_return'] = $summary['total_gain'] + $summary['earnings'];
        if ($summary['cost_basis'] > 0) {
            $summary['unrealized_gain_percent'] = $summary['unrealized_gain'] / $summary['cost_basis'] * 100;
        }
        $open_total = array_sum($open_costs);
        if ($open_total > 0) {
            rsort($open_costs);
            $summary['largest_part_percent'] = $open_costs[0] / $open_total * 100;
            $summary['top5_part_percent'] = array_sum(array_slice($open_costs, 0, 5)) / $open_total * 100;
        }
        return array_map(fn(int|float $value): int|float => is_float($value) ? round($value, 2) : $value, $summary);
    }

    public function incomeByYears(): array
    {
        return Cache::remember('portfolio_'.$this->_portfolio->id.'_income_by_years', 60*60, function (): array {
            $fields = [
                Trade::TYPE_DIVIDEND => 'dividends',
                Trade::TYPE_COUPON => 'coupons',
                Trade::TYPE_FEE => 'fees',
                Trade::TYPE_TAXES => 'taxes',
            ];
            $rows = DB::table('trades')
                ->select([
                    DB::raw('YEAR(FROM_UNIXTIME(created_at)) as year'),
                    'type',
                    'currency_code',
                    DB::raw('SUM(sum) as total'),
                ])
                ->where('portfolio_id', $this->_portfolio->id)
                ->whereIn('type', array_keys($fields))
                ->groupBy(['year', 'type', 'currency_code'])
                ->orderBy('year')
                ->get();
            $data = [];
            foreach ($rows as $row) {
                $year = (int)$row->year;
                $data[$year] ??= ['year' => $year] + array_fill_keys(array_values($fields), 0);
                $data[$year][$fields[(int)$row->type]] += abs($this->toPortfolioCurrency((float)$row->total, $row->currency_code));
            }
            return array_map(
                fn(array $row): array => array_map(fn(int|float $value): int|float => is_float($value) ? round($value, 2) : $value, $row),
                array_values($data)
            );
        });
    }

    private function groupByCost(callable $resolve): array
    {
        $data = [];
        $total = 0;
        foreach ($this->_content as $item) {
            $cost = (float)$item->current_cost;
            if ($cost <= 0) {
                continue;
            }
            [$key, $label] = $resolve($item);
            $data[$key] ??= [
                'label' => $label,
                'currency_sign' => $this->_portfolio->currency_sign,
                'total_cost' => 0,
                'part_percent' => 0,
            ];
            $data[$key]['total_cost'] += $cost;
            $total += $cost;
        }
        foreach ($data as &$row) {
            $row['part_percent'] = round($row['total_cost'] / $total * 100, 2);
            $row['total_cost'] = round($row['total_cost'], 2);
        }
        unset($row);
        usort($data, fn(array $a, array $b): int => $b['total_cost'] <=> $a['total_cost']);
        return $data;
    }

    private function toPortfolioCurrency(float $value, string $currency_code): float
    {
        if ($currency_code == $this->_portfolio->currency_code) {
            return $value;
        }
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        return $currencyService->convert($value, $currency_code, $this->_portfolio->currency_code);
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
