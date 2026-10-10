<?php

namespace App\Http\Requests\CustomAsset;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCustomAsset extends FormRequest
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
            'ticker' => 'required|string|unique:custom_assets,ticker,NULL,id,user_id,'.$user_id.'|max:255',
            'currency_code' => [
                'required', 'string', Rule::in(Currency::codes()),
            ],
            'name' => 'nullable|string|max:255',
            'info' => 'nullable|string',
        ];
    }
}
