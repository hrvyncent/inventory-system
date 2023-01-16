<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    /**
     * Attempt to authenticate the user.
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate() : void
    {
        $this->checkRateLimit();

        if(!Auth::attempt($this->validated(), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Check if the request is rate limited
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    private function checkRateLimit() : void
    {
        if(RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($this->throttleKey()),
                ]),
            ]);
        } 
    }

    /**
     * Get the rate limiting throttle key.
     */
    private function throttleKey() : string
    {
        return Str::transliterate($this->ip().':'.Str::lower($this->input('email')));
    }
}
