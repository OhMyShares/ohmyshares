<?php

namespace App\Http\Requests\Trade;

use App\Models\Trade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class IndexTrade extends FormRequest
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
            'portfolio_id' => [
                'integer',
                Rule::exists('portfolios', 'id')->where(function ($query): void {
                    $query->where('user_id', Auth::user()->id);
                }),
            ],
            'type' => [
                'integer', 'nullable',
                Rule::in(array_keys(Trade::types()))
            ],
            'ticker' => 'string|nullable',
            'offset' => 'integer|nullable',
            'limit' => 'integer|nullable',
            'created_at_begin' => 'integer|date_format:U|nullable',
            'created_at_end' => 'integer|date_format:U|nullable',
        ];
    }
}
