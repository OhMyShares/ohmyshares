<?php

namespace App\Http\Controllers\API;

use App\Components\Uid;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portfolio\StorePortfolio;
use App\Http\Requests\Portfolio\UpdatePortfolio;
use App\Jobs\ImportReport;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\Trade;
use App\Models\User;
use App\Services\ReportImportService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Portfolios', description: "User's portfolios")]
class PortfolioController extends Controller
{
    /**
     * Display a portfolios listing of authorized user.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/portfolios',
        description: "Get user's portfolios list",
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request): array
    {
        /** @var User $user */
        $user = Auth::user();
        $key = 'portfolios_index_'.$user->id;
        return Cache::tags($key)->remember($key, 60*60*48, function () use ($user): array {
            $items = [];
            $portfolios = Portfolio::where('user_id', $user->id)->get();
            foreach ($portfolios as $p) {
                $item = $p->toArray();
                $items[] = $item;
            }
            return $items;
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Portfolio|array|\Illuminate\Http\Response
     */
    #[OA\Post(
        path: '/portfolios',
        description: 'Add portfolio',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['name', 'currency_code', 'account_type'],
                    properties: [
                        new OA\Property(property: 'name', description: 'Portfolio Name', type: 'string'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'account_type', description: 'Account Type', type: 'integer'),
                        new OA\Property(property: 'opened_at', description: 'Portfolio opening date for user', type: 'string', format: 'timestamp', default: '1574953946'),
                        new OA\Property(property: 'fee_percent', description: 'Fee percent, like 0.03', type: 'number', format: 'float', default: '0.00'),
                        new OA\Property(property: 'notes', description: 'Notes', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Portfolios'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function store(StorePortfolio $request): \App\Models\Portfolio
    {
        //$validated = $request->validated();
        /** @var User $user */
        $user = Auth::user();
        Cache::tags('portfolios_index_'.$user->id)->flush();
        $model = new Portfolio;
        $model->fill($request->all());
        $model->user_id = $user->id;
        $model->save();
        return $model;
    }

    /**
     * Display the specified portfolio.
     *
     * @param  string $uid
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/portfolios/{uid}',
        description: "Get user's portfolio info by id",
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function show($uid): array
    {
        $model = $this->findModel($uid);
        $data = $model->toArray();
        $data['currency_sign'] = $model->currencySign();
        return $data;
    }

    /**
     * Display portfolio content.
     *
     * @param string $uid
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/portfolios/{uid}/content',
        description: 'Display portfolio content by tickers',
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function content(Request $request, $uid): array
    {
        $portfolio = $this->findModel($uid);
        $model_arr = $portfolio->toArray();
        $data = $portfolio->content();

        return [
            'content' => $data['content'],
            'summary' => $data['summary'],
            //'analytics' => $data['analytics'],
            'portfolio' => $model_arr,
        ];
    }

    /**
     * Display portfolio analytics.
     *
     * @param string $uid
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/portfolios/{uid}/analytics',
        description: 'Display portfolio analytics',
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function analytics(Request $request, $uid): array
    {
        $portfolio = $this->findModel($uid);
        $model_arr = $portfolio->toArray();
        $data = $portfolio->content();
        $content = $data['content'];
        $analytics = array_merge($data['analytics'], [
            'growth' => $portfolio->calcGrowth(),
            'goal_achievement_percent' => $portfolio->calcGoalAchievementPercent(),
        ]);

        return [
            'content' => $content,
            'analytics' => $analytics,
            'portfolio' => $model_arr,
        ];
    }

    /**
     * Update the specified portfolio in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $uid
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Patch(
        path: '/portfolios/{uid}',
        description: 'Update portfolio',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', description: 'Portfolio Name', type: 'string'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'account_type', description: 'Account Type', type: 'integer'),
                        new OA\Property(property: 'opened_at', description: 'Portfolio opening date for user', type: 'string', format: 'date'),
                        new OA\Property(property: 'fee_percent', description: 'Fee percent, like 0.03', type: 'number', format: 'float'),
                        new OA\Property(property: 'notes', description: 'Notes', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', required: true, in: 'path', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function update(UpdatePortfolio $request, $uid): Portfolio
    {
        /** @var User $user */
        $user = Auth::user();
        Cache::tags('portfolios_index_'.$user->id)->flush();
        $model = $this->findModel($uid);
        $model->update(array_filter($request->all()));
        return $model;
    }

    /**
     * Remove the specified portfolio from storage.
     *
     * @param  string $uid
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Delete(
        path: '/portfolios/{uid}',
        description: "Remove user's portfolio record by uid",
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function destroy($uid): array
    {
        $model = $this->findModel($uid);
        $model->delete();
        return [];
    }

    /**
     * Import trades from broker`s report.
     *
     * @param string $uid
     * @param string $parser
     * @throws \Exception
     */
    #[OA\Post(
        path: '/portfolios/{uid}/import/{parser}',
        description: 'Upload CSV file for import',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'report', description: 'CSV File', type: 'file'),
                    ]
                )
            )
        ),
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'parser', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function import($uid, $parser, ReportImportService $service): void
    {
        $model = $this->findModel($uid);
        abort_unless($service->hasFormat($parser), 422, __('parsers.incorrect_parser'));
        $path = request()->file('report')->store('import');

        ImportReport::dispatch($model, Storage::path($path), $parser);
    }


    /**
     * Clone portfolio (duplicate).
     *
     * @param string $uid
     * @return array
     * @throws \Exception
     */
    #[OA\Post(
        path: '/portfolios/{uid}/clone',
        description: 'Clone portfolio',
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'uid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function clone($uid): Portfolio
    {
        $model = $this->findModel($uid);
        $new = $model->duplicate();
        if (!$new) {
            abort(400, __('messages.portfolio_clone_error'));
        }
        return $new;
    }

    /**
     * Delete portfolio item
     * @param $itemUid
     * @throws Exception
     */
    #[OA\Delete(
        path: '/portfolio-item/{itemUid}',
        description: "Remove user's portfolio item record by uid",
        security: [['bearerAuth' => []]],
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'itemUid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function itemDelete($itemUid): array
    {
        $model = $this->findItemModel($itemUid);
        $model->delete();
        return [];
    }

    #[OA\Patch(
        path: '/portfolio-items/{itemUid}/ticker',
        description: 'Replace ticker in all trades of portfolio item',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['ticker'],
                    properties: [
                        new OA\Property(property: 'ticker', description: 'New ticker, like BA', type: 'string'),
                        new OA\Property(property: 'exchange', description: 'New exchange', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Portfolios'],
        parameters: [
            new OA\Parameter(name: 'itemUid', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation errors'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function itemChangeTicker(Request $request, $itemUid): array
    {
        $model = $this->findItemModel($itemUid);
        abort_if($model->ticker === Trade::TICKER_MONEY, 422, __('messages.money_ticker_change_error'));
        $validated = $request->validate([
            'ticker' => ['required', 'string', 'max:100', Rule::notIn([Trade::TICKER_MONEY])],
            'exchange' => ['nullable', 'string', 'max:100'],
        ]);
        return $model->changeTicker($validated['ticker'], $validated['exchange'] ?? null)->toArray();
    }


    /**
     * Find or fail method for Portfolio model
     * @param $uid
     */
    public function findModel($uid): Portfolio
    {
        /** @var User $user */
        $user = Auth::user();
        $user->touchLastActive();
        $id = Uid::decode($uid);
        $model = Portfolio::find($id);
        if ($model && $model->user_id == $user->id) {
            return $model;
        }
        abort(404, __('messages.not_found_portfolio'));
    }

    /**
     * Find or fail method for PortfolioItem model
     * @param $itemUid
     */
    public function findItemModel($itemUid): PortfolioItem
    {
        /** @var User $user */
        $user = Auth::user();
        $user->touchLastActive();
        $id = Uid::decode($itemUid);
        $model = PortfolioItem::find($id);
        if ($model && $model->portfolio && $model->portfolio->user_id == $user->id) {
            return $model;
        }
        abort(404, __('messages.not_found_portfolio_item'));
    }
}
