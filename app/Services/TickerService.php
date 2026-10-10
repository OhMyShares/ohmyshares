<?php
namespace App\Services;

use App\Components\ArrayUtils;
use App\Models\Ticker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use OhMyShares\Plugin\Enums\AssetType;

class TickerService
{
    const PROVIDER_SELF = 0;

    /**
     * Get quotes
     */
    public function quote(string $ticker, int $asset = AssetType::Shares->value)
    {
        $ticker = mb_strtolower($ticker);
        $key = 'quote_'.$ticker.'_'.$asset;
        $duration = 60*60*2; // 2 hours
        if (!config('app.markets_cache_enabled')) { $duration = 1; }
        $price = Cache::remember($key, $duration, function () use ($ticker, $asset) {
            $res = ['price' => 0];

            // checking if custom asset first
            if ($asset == AssetType::Custom->value) {
                $cs_asset = Auth::user()->customAssets()
                    ->where('ticker', $ticker)
                    ->first(['id', 'ticker as symbol', 'name', 'currency_code']);
                if ($cs_asset) {
                    $cs_asset->makeHidden(['id']);
                }
                return $cs_asset;
            }

            // looking in Tickers table
            $t = Ticker::findFuzzy($ticker);
            if ($t) {
                $res = [
                    'symbol' => $t->ticker,
                    'price' => null, // TODO: walk thru plugins to get actual price
                ];
            }

            ArrayUtils::allowKeys($res, ['symbol', 'price']);
            return $res;
        });
        if (!$price) {
            Cache::forget($key);
        }
        return $price;
    }


    /**
     * Search ticker
     */
    public function search($q, $asset = null, $currency_code = null)
    {
        $q = trim(mb_strtolower($q));
        // cache for a week
        $duration = 60*60*24*7;
        $key = 'search_'.$q.'_'.$asset;
        if (!config('app.markets_cache_enabled')) { $duration = 1; }
        return Cache::remember($key, $duration, function () use ($q, $asset)
        {
            // checking if custom asset first
            if ($asset == AssetType::Custom->value) {
                $cs_items = Auth::user()->customAssets()
                    ->where('ticker', 'like', "%$q%")
                    ->orWhere('name', 'like', "%$q%")
                    ->get(['id', 'ticker as symbol', 'name', 'currency_code'])
                    ->makeHidden(['id', 'currency_code']);
                ArrayUtils::addToAll($cs_items, 'provider', self::PROVIDER_SELF);
                return $cs_items;
            }

            // locally items
            $local_items = [];
            $models = Ticker::getSuggestions($q);
            if ($models->count()) {
                foreach ($models as $model) {
                    $local_items[] = [
                        'symbol' => $model->ticker,
                        'exchange' => $model->exchange,
                        'name' => $model->name,
                        'currency_code' => $model->currency_code,
                        'provider' => self::PROVIDER_SELF,
                    ];
                }
            }

            // TODO: walk thru plugins to find more tickers
            // $items = array_merge($local_items, $plugin_items);
            $items = $local_items;

            if ($items) {
                foreach ($items as &$item) {
                    ArrayUtils::allowKeys($item, ['symbol', 'name', 'provider', 'exchange']);
                }
            }

            return $items;
        });
    }
}
