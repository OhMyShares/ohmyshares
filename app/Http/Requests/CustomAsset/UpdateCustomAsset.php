<?php

namespace App\Http\Requests\CustomAsset;

use App\Enums\Currency;
use App\Models\CustomAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCustomAsset extends FormRequest
{
    private $model;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->model = CustomAsset::find($this->route('custom_asset'));
        return $this->model && $this->model->user_id == Auth::user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'currency_code' => [
                'nullable', 'string', Rule::in(Currency::codes()),
            ],
            'name' => 'nullable|string|max:255',
            'info' => 'nullable|string',
        ];

        // check unique if ticker changed
        if ($this->model->ticker != $this->input('ticker')) {
            $user_id = Auth::user()->id;
            $rules['ticker'] = [
                'nullable',
                'string',
                'unique:custom_assets,ticker,NULL,id,user_id,'.$user_id,
                'max:255',
            ];
        }

        return $rules;
    }
}
