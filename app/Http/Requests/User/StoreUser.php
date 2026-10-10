<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->role === User::ROLE_ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8|max:255',
            'role' => ['nullable', 'integer', Rule::in(array_keys(User::roleLabels()))],
            'locale' => ['nullable', 'string', Rule::in(config('app.locales_available'))],
            'is_test' => 'nullable|boolean',
            'is_ignore_funds_sufficiency' => 'nullable|boolean',
        ];
    }
}
