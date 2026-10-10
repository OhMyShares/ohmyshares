<?php

namespace App\Http\Controllers\API;

use App\Components\Uid;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChartTrade;
use App\Http\Requests\Trade\IndexTrade;
use App\Http\Requests\Trade\StoreTrade;
use App\Http\Requests\Trade\UpdateTrade;
use App\Models\PortfolioItem;
use App\Models\Trade;
use App\QueryFilters\TradeFilters;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Trades', description: "User's trades")]
class TradeController extends Controller
{
    /**
     * Display a trades listing of authorized user.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/trades',
        description: "Get user's trades list",
        security: [['bearerAuth' => []]],
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'portfolio_uid', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'ticker', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'asset', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'created_at_begin', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'created_at_end', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'uid', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(IndexTrade $request, TradeFilters $filters): array
    {
        $user_id = Auth::user()->id;
        $q_count = Trade::filterBy($filters)
            ->where('user_id', $user_id);
        $q_data = (clone $q_count);
        $limit = $request->get('limit');
        $offset = $request->get('offset');
        if ($limit) {
            $q_data->limit($limit)->offset($offset);
        }
        $result = [
            'data' => $q_data->get(),
            'total' => $q_count->count(),
        ];

        $uid = $request->get('uid');
        if ($uid) {
            $item = PortfolioItem::find(Uid::decode($uid));
            if ($item && $item->portfolio && $item->portfolio->user_id == $user_id) {
                $result['summary'] = $item->toArray();
                $result['summary']['portfolio'] = $item->portfolio->toArray();
            }
        }

        return $result;
    }

    /**
     * Display a tickers listing used in user's trades.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/trades/tickers',
        description: 'Get tickers list used in trades',
        security: [['bearerAuth' => []]],
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'portfolio_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function tickers(Request $request): array
    {
        $request->validate([
            'portfolio_id' => [
                'integer',
                Rule::exists('portfolios', 'id')->where(function ($query): void {
                    $query->where('user_id', Auth::user()->id);
                }),
            ],
        ]);
        $portfolio_id = $request->get('portfolio_id');
        $q = Trade::select('ticker')
            ->distinct()
            ->where('user_id', Auth::user()->id);
        if ($portfolio_id) {
            $q->where('portfolio_id', $portfolio_id);
        }
        $data = $q->get();
        $list = [];
        foreach ($data as $key => $value) {
            $ticker = Arr::get($value, 'ticker');
            if ($ticker != Trade::TICKER_MONEY) {
                $list[] = [
                    'symbol' => $ticker,
                    'name' => $ticker,
                ];
            }
        }
        return $list;
    }

    /**
     * Display a currencies listing used in user's trades.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/trades/currencies',
        description: 'Get currencies list used in trades',
        security: [['bearerAuth' => []]],
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'portfolio_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function currencies(Request $request): Collection
    {
        $request->validate([
            'portfolio_id' => [
                'integer',
                Rule::exists('portfolios', 'id')->where(function ($query): void {
                    $query->where('user_id', Auth::user()->id);
                }),
            ],
        ]);
        $portfolio_id = $request->get('portfolio_id');
        $q = Trade::select('currency_code')
            ->distinct()
            ->where('user_id', Auth::user()->id);
        if ($portfolio_id) {
            $q->where('portfolio_id', $portfolio_id);
        }
        return $q->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Trade|array|\Illuminate\Http\Response
     */
    #[OA\Post(
        path: '/trades',
        description: 'Add trade',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'portfolio_id', description: 'Portfolio ID', type: 'integer'),
                        new OA\Property(property: 'type', description: 'Type of operation (buy, sell, fee, etc...)', type: 'integer'),
                        new OA\Property(property: 'asset', description: 'Type of asset (share, bond, etc...)', type: 'integer'),
                        new OA\Property(property: 'created_at', description: 'Date of operation', type: 'integer'),
                        new OA\Property(property: 'ticker', description: 'AAPL, MSFT, GAZP.ME, etc...', type: 'string'),
                        new OA\Property(property: 'cost', description: 'Cost of one share', type: 'number', format: 'float'),
                        new OA\Property(property: 'shares', description: 'Quantity', type: 'integer'),
                        new OA\Property(property: 'sum', description: 'Whole sum of trade', type: 'number', format: 'float'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'fee', description: 'Fee value for current trade', type: 'number', format: 'float'),
                        new OA\Property(property: 'fee_currency_code', description: 'Fee currency code', type: 'string'),
                        new OA\Property(property: 'is_wo_money', description: 'Do not affect on money flag', type: 'boolean'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Trades'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function store(StoreTrade $request): \App\Models\Trade
    {
        //$validated = $request->validated();
        $model = new Trade;
        $model->fill($request->all());
        $model->user_id = Auth::user()->id;
        $model->save();
        return $model;
    }

    /**
     * Display the specified trade.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/trades/{id}',
        description: "Get user's trade info by id",
        security: [['bearerAuth' => []]],
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function show($id): Trade
    {
        $model = Trade::find($id);
        if ($model && $model->user_id == Auth::user()->id) {
            return $model;
        }
        abort(404, __('messages.not_found_trade'));
    }

    /**
     * Update the specified trade in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Patch(
        path: '/trades/{id}',
        description: 'Update trade',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'portfolio_id', description: 'Portfolio ID', type: 'integer'),
                        new OA\Property(property: 'type', description: 'Type of operation (buy, sell, fee, etc...)', type: 'integer'),
                        new OA\Property(property: 'asset', description: 'Type of asset (share, bond, etc...)', type: 'integer'),
                        new OA\Property(property: 'created_at', description: 'Date of operation', type: 'integer'),
                        new OA\Property(property: 'ticker', description: 'AAPL, MSFT, GAZP.ME, etc...', type: 'string'),
                        new OA\Property(property: 'cost', description: 'Cost of one share', type: 'number', format: 'float'),
                        new OA\Property(property: 'shares', description: 'Quantity', type: 'integer'),
                        new OA\Property(property: 'sum', description: 'Whole sum of trade', type: 'number', format: 'float'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'fee', description: 'Fee value for current trade', type: 'number', format: 'float'),
                        new OA\Property(property: 'fee_currency_code', description: 'Fee currency code', type: 'string'),
                        new OA\Property(property: 'is_wo_money', description: 'Do not affect on money flag', type: 'boolean'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function update(UpdateTrade $request, $id): Trade
    {
        $trade = Trade::find($id);
        if (!is_null($trade->related_trade_id)) {
            abort(406, 'Unable to update related trade. Please, update parent trade for that.');
        }
        $trade->update($request->all());
        return $trade;
    }

    /**
     * Remove the specified trade from storage.
     *
     * @param  int  $id
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Delete(
        path: '/trades/{id}',
        description: "Remove user's trade record by id",
        security: [['bearerAuth' => []]],
        tags: ['Trades'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function destroy($id): array
    {
        $trade = Trade::find($id);
        $deleted = $trade->delete();
        return [];
    }
}
