<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ValidHCaptcha implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $secret = config('services.hcaptcha.secret');
        // bypass validation if secret is not set
        if (!$secret) {
            return true;
        }
        $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret' => $secret,
            'response' => $value,
        ]);
        $isSuccess = $response->json('success');
        if ($response->successful() && $isSuccess) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'CAPTCHA is invalid.';
    }
}
