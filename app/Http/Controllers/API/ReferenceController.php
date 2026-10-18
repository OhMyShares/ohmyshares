<?php

namespace App\Http\Controllers\API;

use App\Enums\Currency;
use App\Http\Controllers\Controller;
use App\Jobs\GetHistory;
use App\Models\CustomAsset;
use App\Models\Portfolio;
use App\Models\Ticker;
use App\Models\Trade;
use App\Services\CurrencyService;
use App\Services\ReportImportService;
use App\Services\TickerService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use OhMyShares\Plugin\Enums\AssetType;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'References', description: 'Dictionaries')]
class ReferenceController extends Controller
{
    /**
     * List of currencies
     */
    #[OA\Get(
        path: '/reference/currency',
        description: 'Currencies list',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        parameters: [
            new OA\Parameter(name: 'available', in: 'query', required: false, description: 'Return only currencies having exchange rates', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function currency(Request $request): array
    {
        $list = Currency::list();
        if ($request->boolean('available')) {
            /** @var CurrencyService $currencyService */
            $currencyService = App::make('currencyService');
            $codes = $currencyService->currencies();
            $list = array_values(array_filter(
                $list,
                fn(array $item): bool => in_array($item['code'], $codes, true)
            ));
        }
        return $list;
    }

    /**
     * List of currency rates
     */
    #[OA\Get(
        path: '/reference/currency-rates',
        description: 'Currency rates list',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function currencyRates(Request $request): array
    {
        $list = [];
        /** @var CurrencyService $currencyService */
        $currencyService = App::make('currencyService');
        $codes = array_intersect(Currency::codes(), $currencyService->currencies());
        $signs = Currency::signs();
        foreach ($codes as $fromCode) {
            $fromSign = $signs[$fromCode];
            foreach ($codes as $toCode) {
                $list[] = [
                    'from_name' => $fromSign,
                    'from_code' => $fromCode,
                    'to_name' => $signs[$toCode],
                    'to_code' => $toCode,
                    'rate' => round($currencyService->convert(1, $fromCode, $toCode), 5),
                ];
            }
        }
        return $list;
    }

    /**
     * List of account types
     */
    #[OA\Get(
        path: '/reference/account-type',
        description: 'Portfolio account type',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function accountType(Request $request): array
    {
        return Cache::remember('account_types', 60*60*48, function (): array {
            $list = [];
            foreach (Portfolio::accountTypes() as $key => $value) {
                $list[] = [
                    'id' => $key,
                    'name' => $value,
                ];
            }
            return $list;
        });
    }

    /**
     * List of trade types
     */
    #[OA\Get(
        path: '/reference/trade-type',
        description: 'Trade type',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function tradeType(Request $request): array
    {
        return Cache::remember('trade_types_' . App::getLocale(), 60*60*48, function (): array {
            $list = [];
            foreach (Trade::types() as $key => $value) {
                $list[] = [
                    'id' => $key,
                    'name' => $value,
                    'hide_sum' => in_array($key, [Trade::TYPE_BUY, Trade::TYPE_SELL], true),
                    'hide_cost' => in_array($key, [Trade::TYPE_CREDIT, Trade::TYPE_DEBIT, Trade::TYPE_FEE, Trade::TYPE_DIVIDEND, Trade::TYPE_COUPON, Trade::TYPE_TAXES], true),
                    'hide_shares' => in_array($key, [Trade::TYPE_CREDIT, Trade::TYPE_DEBIT, Trade::TYPE_FEE, Trade::TYPE_DIVIDEND, Trade::TYPE_COUPON, Trade::TYPE_TAXES], true),
                    'hide_ticker' => in_array($key, [Trade::TYPE_CREDIT, Trade::TYPE_DEBIT, Trade::TYPE_FEE, Trade::TYPE_TAXES], true),
                    'hide_fee' => !in_array($key, [Trade::TYPE_BUY, Trade::TYPE_SELL], true),
                    'hide_fee_currency_code' => !in_array($key, [Trade::TYPE_BUY, Trade::TYPE_SELL], true),
                    //'hide_asset' => in_array($key, [Trade::TYPE_CREDIT, Trade::TYPE_DEBIT, Trade::TYPE_FEE, Trade::TYPE_TAXES], true),
                    'linked_asset' => Arr::get(Trade::linkTypeAsset(), $key),
                    'default' => ($key === Trade::TYPE_BUY),
                ];
            }
            return $list;
        });
    }

    /**
     * List of trade asset types
     */
    #[OA\Get(
        path: '/reference/trade-asset',
        description: 'Trade asset type',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function tradeAsset(Request $request): array
    {
        $list = [];
        foreach (AssetType::array() as $key => $value) {
            $list[] = [
                'id' => $key,
                'name' => $value,
                'linked_type' => Arr::get(Trade::linkAssetType(), $key),
                'default' => ($key == AssetType::Shares->value),
            ];
        }
        return $list;
    }

    /**
     * Ticker searching
     */
    #[OA\Get(
        path: '/reference/ticker',
        description: 'Ticker searching',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        parameters: [
            new OA\Parameter(name: 'q', description: 'msft, aapl, gazp, etc..', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'asset', description: 'Asset type id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function ticker(Request $request): \Illuminate\Support\Collection|array
    {
        $request->validate([
            'q' => 'string',
            'asset' => 'integer',
        ]);
        $q = $request->get('q');
        $asset = $request->get('asset');
        /** @var TickerService $tickerService */
        $tickerService = App::make('tickerService');
        $res = $tickerService->search($q, $asset);

        // empty result if false
        return $res ?: [];
    }

    /**
     * Ticker price info
     */
    #[OA\Get(
        path: '/reference/quote',
        description: 'Get price by ticker',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        parameters: [
            new OA\Parameter(name: 'q', description: 'aapl', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'asset', description: 'Asset type code', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function quote(Request $request): array
    {
        $currency_code = 'USD';
        $q = $request->get('q');
        $asset = (int)$request->get('asset');
        switch ($asset) {
            case AssetType::Custom->value:
                $ticker = CustomAsset::where('ticker', $q)
                    ->where('user_id', Auth::user()->id)
                    ->first();
                if ($ticker) {
                    return [
                        'price' => (float)$ticker->lastPrice(),
                        'currency_code' => $ticker->currency_code,
                    ];
                }
                break;

            default:
                $exchange = $request->get('exchange');
                $ticker = $exchange !== null
                    ? Ticker::findModel($q, $exchange)
                    : Ticker::findFuzzy($q);
                if ($ticker) {
                    $currency_code = $ticker->currency_code;
                    $last_price = $ticker->lastPrice();
                    if ($last_price) {
                        if ($ticker->isOutdated()) {
                            GetHistory::dispatch($ticker->ticker, exchange: $ticker->exchange);
                        }
                        return [
                            'price' => (float)$last_price,
                            'currency_code' => $currency_code,
                        ];
                    } else {
                        // if no price found
                        GetHistory::dispatch($ticker->ticker, exchange: $ticker->exchange);
                    }
                }
                /** @var TickerService $tickerService */
                $tickerService = App::make('tickerService');
                $res = $tickerService->quote($q, $asset);
                if ($res) {
                    if (!Arr::has($res, 'currency_code') && $currency_code) {
                        $res['currency_code'] = $currency_code;
                    }
                    $res['price'] = (float)Arr::get($res, 'price');
                    return $res;
                }
                break;
        }
        return []; //empty result
    }


    /**
     * Available parsers list
     */
    #[OA\Get(
        path: '/reference/parsers',
        description: 'Available parsers list',
        security: [['bearerAuth' => []]],
        tags: ['References'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function parsers(ReportImportService $service): array
    {
        return $service->formats();
    }
}
