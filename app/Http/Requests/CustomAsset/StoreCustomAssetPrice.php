<?php

namespace App\Http\Requests\CustomAsset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCustomAssetPrice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user_id = Auth::user()->id;
        return [
            'custom_asset_id' => [
                'required',
                Rule::exists('custom_assets', 'id')->where(function ($query): void {
                    $query->where('user_id', Auth::user()->id);
                }),
            ],
            'created_at' => 'sometimes|integer|nullable',
            'price' => [
                'required', 'numeric',
            ],
        ];
    }
}
