<?php

namespace App\Http\Requests\Ticker;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OhMyShares\Plugin\enums\TickerType;

class UpdateTickerRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticker' => 'required|max:255',
            'name' => 'required|max:255',
            'isin' => 'max:255',
            'currency_code' => [
                'nullable', 'string',
                Rule::in(Currency::codes())
            ],
            'type' => Rule::in(TickerType::values()),
        ];
    }
}
