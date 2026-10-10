<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\GetHistory;
use App\Models\Ticker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OhMyShares\Plugin\Enums\TickerType;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tickers', description: "Ticker's info")]
class TickerController extends Controller
{

    /**
     * Get list of tickers (companies)
     */
    #[OA\Get(
        path: '/tickers/list',
        description: 'Get tickers list',
        security: [['bearerAuth' => []]],
        tags: ['Tickers'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request): \Illuminate\Support\Collection|array
    {
        $request->validate([
            'q' => 'string',
            'type' => [
                'integer', 'nullable',
                Rule::in(array_keys(Ticker::typeLabels()))
            ],
        ]);
        $q = $request->get('q');
        $type = $request->get('type', TickerType::Stock->value);
        if ($q) {
            $data = Ticker::getSuggestions($q);
            $data->makeHidden('is_hidden');
        } else {
            $data = DB::table('portfolio_items')
                ->selectRaw('count(*) as popularity_index, tickers.ticker, tickers.exchange, tickers.name')
                ->join('tickers', function ($join): void {
                    $join->on('portfolio_items.ticker', '=', 'tickers.ticker')
                        ->on('portfolio_items.exchange', '=', 'tickers.exchange');
                })
                ->where('type', $type)
                ->groupBy('ticker', 'exchange', 'name', 'isin')
                ->orderByDesc('popularity_index')
                ->limit(30)
                ->get();
        }
        return $data;
    }


    /**
     * Get ticker info
     */
    #[OA\Get(
        path: '/tickers/{ticker}/info',
        description: "Get ticker's dividends info",
        security: [['bearerAuth' => []]],
        tags: ['Tickers'],
        parameters: [
            new OA\Parameter(name: 'ticker', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function info(Request $request, $ticker): array
    {
        $t = Ticker::findModel($ticker, $request->query('exchange'));
        if ($t) {
            $t->makeHidden(['is_hidden']);
            $data = $t->toArray();
            $data['price'] = $t->lastPrice();
            return $data;
        }
        abort(404, __('messages.not_found_ticker'));
    }


    /**
     * Get history data for ticker
     */
    #[OA\Get(
        path: '/tickers/{ticker}/history',
        description: 'Get history data for ticker',
        security: [['bearerAuth' => []]],
        tags: ['Tickers'],
        parameters: [
            new OA\Parameter(name: 'ticker', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'begin', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'end', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function history(Request $request, $ticker): \Illuminate\Support\Collection|array
    {
        $request->validate([
            'begin' => 'date|date_format:Y-m-d|nullable',
            'end' => 'date|date_format:Y-m-d|nullable',
            'asset' => 'integer',
        ]);
        $begin = $request->get('begin');
        $end = $request->get('end');
        $asset = $request->get('asset');
        $duration = 10;
        if (config('app.env') === 'production') {
            $duration = 60 * 10;
        }
        $exchange = $request->query('exchange');
        return Cache::remember('history' . $ticker . $begin . $end . $asset . $exchange, $duration,
            function () use ($request, $ticker, $begin, $end, $asset, $exchange) {
                $history = [];
                $t = Ticker::findModel($ticker, $exchange);
                if ($t) {
                    if ($t->isHistoryMissingSinceFirstTrade()) {
                        GetHistory::dispatch($t->ticker, exchange: $t->exchange);
                    }
                    $history = $t->history($begin, $end);
                    $history->makeHidden('ticker');
                }
                return $history;
            });

    }


    /**
     * Get dividends list for ticker
     */
    #[OA\Get(
        path: '/tickers/{ticker}/dividends',
        description: "Get ticker's dividends info",
        security: [['bearerAuth' => []]],
        tags: ['Tickers'],
        parameters: [
            new OA\Parameter(name: 'ticker', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function dividends(Request $request, $ticker): Collection|array
    {
        $t = Ticker::findModel($ticker, $request->query('exchange'));
        if ($t) {
            GetHistory::dispatch($t->ticker, exchange: $t->exchange);
            return $t->dividends();
        }
        abort(404, __('messages.not_found_ticker'));
    }
}
