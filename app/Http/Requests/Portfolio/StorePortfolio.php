<?php

namespace App\Http\Requests\Portfolio;

use App\Enums\Currency;
use App\Models\Portfolio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolio extends FormRequest
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
        return [
            'name' => 'required|string',
            'currency_code' => [
                'required', 'string', Rule::in(Currency::codes()),
            ],
            'account_type' => [
                'required', 'integer', Rule::in(array_keys(Portfolio::accountTypes())),
            ],
            'fee_percent' => 'sometimes|nullable|numeric|min:0|max:10',
            'opened_at' => 'sometimes|nullable|date_format:U',
            'notes' => 'string|nullable',
        ];
    }
}
