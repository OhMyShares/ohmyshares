<?php

namespace App\Http\Requests\Trade;

use App\Enums\Currency;
use App\Models\Portfolio;
use App\Models\Trade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use OhMyShares\Plugin\Enums\AssetType;

class StoreTrade extends FormRequest
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
            'portfolio_uid' => [
                'required',
                Rule::in(Arr::pluck(Auth::user()->portfolios, 'uid')),
            ],
            'type' => [
                'integer', 'nullable',
                Rule::in(array_keys(Trade::types()))
            ],
            'asset' => [
                'integer', 'nullable',
                Rule::in(AssetType::values())
            ],
            'currency_code' => [
                'string', 'nullable',
                Rule::in(Currency::codes())
            ],
            'created_at' => 'sometimes|integer|nullable',
            'ticker' => [
                'string', 'nullable',
                Rule::requiredIf(fn(): bool => in_array($this->type, [
                    Trade::TYPE_BUY, Trade::TYPE_SELL, Trade::TYPE_DIVIDEND
                ])),
            ],
            'exchange' => 'string|nullable|max:100',
            'cost' => [
                'numeric', 'nullable',
                Rule::requiredIf(fn(): bool => in_array($this->type, [
                    Trade::TYPE_BUY, Trade::TYPE_SELL
                ])),
            ],
            'shares' => [
                'numeric', 'nullable',
                Rule::requiredIf(fn(): bool => in_array($this->type, [
                    Trade::TYPE_BUY, Trade::TYPE_SELL
                ])),
            ],
            'sum' => [
                'numeric', 'nullable',
                Rule::requiredIf(fn(): bool => in_array($this->type, [
                    Trade::TYPE_DEBIT, Trade::TYPE_CREDIT, Trade::TYPE_FEE, Trade::TYPE_DIVIDEND
                ])),
            ],
            'fee' => 'numeric|nullable',
            'fee_currency_code' => [
                'string', 'nullable',
                Rule::in(Currency::codes())
            ],
            'notes' => 'string|nullable',
            'is_wo_money' => 'boolean|nullable',
        ];
    }
}
