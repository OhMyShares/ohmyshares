<?php

namespace App\Http\Requests\Portfolio;

use App\Components\Uid;
use App\Enums\Currency;
use App\Models\Portfolio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePortfolio extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $id = Uid::decode($this->route('portfolio'));
        $model = Portfolio::find($id);
        return $model && $model->user_id == Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string',
            'currency_code' => [
                'nullable',
                'string', Rule::in(Currency::codes()),
            ],
            'account_type' => [
                'nullable',
                'integer', Rule::in(array_keys(Portfolio::accountTypes())),
            ],
            'fee_percent' => 'nullable|numeric|min:0|max:10',
            'opened_at' => 'nullable|date_format:U',
            'notes' => 'nullable|string',
        ];
    }
}
