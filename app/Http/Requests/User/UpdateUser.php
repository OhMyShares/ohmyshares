<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUser extends FormRequest
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
        $user = $this->route('user');
        $roles = $user instanceof User && $user->is(Auth::user())
            ? [User::ROLE_ADMIN]
            : array_keys(User::roleLabels());

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => 'nullable|string|min:8|max:255',
            'role' => ['sometimes', 'required', 'integer', Rule::in($roles)],
            'locale' => ['nullable', 'string', Rule::in(config('app.locales_available'))],
            'is_test' => 'sometimes|boolean',
            'is_ignore_funds_sufficiency' => 'sometimes|boolean',
        ];
    }
}
