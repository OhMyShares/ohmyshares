<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomAsset\StoreCustomAssetPrice;
use App\Http\Requests\CustomAsset\UpdateCustomAssetPrice;
use App\Models\CustomAssetPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class CustomAssetPriceController extends Controller
{
    /**
     * Display a custom assets listing of authorized user.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/custom-assets-prices',
        description: "Get user's custom assets list",
        security: [['bearerAuth' => []]],
        tags: ['CustomAssets'],
        parameters: [
            new OA\Parameter(name: 'custom_asset_id', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request): Collection
    {
        $request->validate([
            'custom_asset_id' => 'required|integer',
        ]);
        return CustomAssetPrice::where('custom_asset_id', $request->get('custom_asset_id'))
            ->join('custom_assets', 'custom_assets_prices.custom_asset_id', '=', 'custom_assets.id')
            ->where('custom_assets.user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get(['custom_assets_prices.id', 'created_at', 'price', 'custom_asset_id']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return CustomAssetPrice|array|\Illuminate\Http\Response
     */
    #[OA\Post(
        path: '/custom-assets-prices',
        description: 'Add custom asset price',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['custom_asset_id', 'price'],
                    properties: [
                        new OA\Property(property: 'custom_asset_id', description: 'Custom asset id', type: 'integer'),
                        new OA\Property(property: 'created_at', description: 'Created At', type: 'integer'),
                        new OA\Property(property: 'price', description: 'Price', type: 'number', format: 'float'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['CustomAssets'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function store(StoreCustomAssetPrice $request): \App\Models\CustomAssetPrice
    {
        $model = new CustomAssetPrice();
        $model->fill($request->all());
        $model->save();
        return $model;
    }

    /**
     * Update the specified custom asset in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Patch(
        path: '/custom-assets-prices/{id}',
        description: 'Update custom asset price',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'custom_asset_id', description: 'Custom asset id', type: 'integer'),
                        new OA\Property(property: 'created_at', description: 'Created At', type: 'integer'),
                        new OA\Property(property: 'price', description: 'Price', type: 'number', format: 'float'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['CustomAssets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function update(UpdateCustomAssetPrice $request, $id): CustomAssetPrice
    {
        $model = $this->findModel($id);
        $model->update(array_filter($request->all()));
        return $model;
    }

    /**
     * Display the specified custom asset.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/custom-assets-prices/{id}',
        description: "Get user's custom asset price info by id",
        security: [['bearerAuth' => []]],
        tags: ['CustomAssets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function show($id): \App\Models\CustomAssetPrice
    {
        return $this->findModel($id);
    }

    /**
     * Remove the specified custom asset from storage.
     *
     * @param  int  $id
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Delete(
        path: '/custom-assets-prices/{id}',
        description: "Remove user's custom asset",
        security: [['bearerAuth' => []]],
        tags: ['CustomAssets'],
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
        $model = $this->findModel($id);
        $model->delete();
        return [];
    }


    /**
     * Find or fail method
     * @param $id
     */
    public function findModel($id): CustomAssetPrice
    {
        $model = CustomAssetPrice::find($id);
        if ($model && $model->asset && $model->asset->user_id == Auth::user()->id) {
            return $model;
        }
        abort(404, 'Price not found.');
    }
}
