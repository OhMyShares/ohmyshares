<?php

namespace App\Http\Requests\CustomAsset;

use App\Models\CustomAssetPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCustomAssetPrice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $model = CustomAssetPrice::find($this->route('custom_assets_price'));
        return $model && $model->asset && $model->asset->user_id == Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'custom_asset_id' => [
                'nullable',
                Rule::exists('custom_assets', 'id')->where(function ($query): void {
                    $query->where('user_id', Auth::user()->id);
                }),
            ],
            'created_at' => 'sometimes|integer|nullable',
            'price' => [
                'nullable', 'numeric',
            ],
        ];
    }
}
