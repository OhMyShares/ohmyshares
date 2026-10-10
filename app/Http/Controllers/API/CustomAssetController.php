<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomAsset\StoreCustomAsset;
use App\Http\Requests\CustomAsset\UpdateCustomAsset;
use App\Models\CustomAsset;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'CustomAssets', description: "User's custom assets & prices")]
class CustomAssetController extends Controller
{
    /**
     * Display a custom assets listing of authorized user.
     *
     * @return array|\Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/custom-assets',
        description: "Get user's custom assets list",
        security: [['bearerAuth' => []]],
        tags: ['CustomAssets'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request): Collection
    {
        return CustomAsset::where('user_id', Auth::user()->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return CustomAsset|Portfolio|array|\Illuminate\Http\Response
     */
    #[OA\Post(
        path: '/custom-assets',
        description: 'Add custom asset',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['ticker', 'currency_code'],
                    properties: [
                        new OA\Property(property: 'ticker', description: 'Ticker/ISIN or asset name', type: 'string'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'name', description: 'Asset name', type: 'string'),
                        new OA\Property(property: 'info', description: 'Asset info description', type: 'string'),
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
    public function store(StoreCustomAsset $request): \App\Models\CustomAsset
    {
        $model = new CustomAsset;
        $model->fill($request->all());
        $model->user_id = Auth::user()->id;
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
        path: '/custom-assets/{id}',
        description: 'Update custom asset',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'ticker', description: 'Ticker/ISIN or asset name', type: 'string'),
                        new OA\Property(property: 'currency_code', description: 'Currency code', type: 'string'),
                        new OA\Property(property: 'name', description: 'Asset name', type: 'string'),
                        new OA\Property(property: 'info', description: 'Asset info description', type: 'string'),
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
    public function update(UpdateCustomAsset $request, $id): CustomAsset
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
        path: '/custom-assets/{id}',
        description: "Get user's custom asset info by id",
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
    public function show($id): \App\Models\CustomAsset
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
        path: '/custom-assets/{id}',
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
     */
    public function findModel($id): CustomAsset
    {
        $model = CustomAsset::find($id);
        if ($model && $model->user_id == Auth::user()->id) {
            return $model;
        }
        abort(404, 'Asset not found.');
    }
}
