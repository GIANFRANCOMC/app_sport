<?php

namespace App\Http\Requests\System\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\{Auth, Hash, RateLimiter};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Helpers\System\Utilities;
use App\Models\System\Organizations\{Company, User};

class LoginRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {

        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {

        return [
            "email" => ["required", "string", "email"],
            "password" => ["required", "string"],
            "company_id" => ["required", "integer"]
        ];

    }

    public function messages() {

        return [

        ];

    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void {

        $this->ensureIsNotRateLimited();

        $credentials = $this->only("email", "password");
        $companyId   = $this->input("company_id");

        $company = Company::where("id", $companyId)
                          ->first();

        // Check if the company is active
        if(!Utilities::isDefined($company) || $company->status !== "active") {

            throw new HttpResponseException(
                redirect("/".Utilities::companyLoginQuery($companyId))
            );

        }

        $user = User::where("email", $credentials["email"])
                    ->where("company_id", $companyId)
                    ->whereIn("status", ["active"])
                    ->with(["company"])
                    ->first();

        // Attempt to authenticate the user
        if(!Utilities::isDefined($user) || !Hash::check($credentials["password"], $user->password)) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                "email" => trans("auth.failed")
            ]);

        }

        // Captcha
        $captchaResponse = $this->input("cf-turnstile-response");
        $secret = config("app.CAPTCHA_KEY_BACKEND");

        $context = stream_context_create([
            "http" => [
                "method"  => "POST",
                "header"  => "Content-type: application/x-www-form-urlencoded",
                "content" => http_build_query([
                    "secret"   => $secret,
                    "response" => $captchaResponse,
                    "remoteip" => $_SERVER["REMOTE_ADDR"],
                ]),
                "ignore_errors" => true
            ]
        ]);

        $response = file_get_contents("https://challenges.cloudflare.com/turnstile/v0/siteverify", false, $context);

        if(!$response || !$json = json_decode($response)) {

            throw ValidationException::withMessages([
                "captcha" => trans("auth.captcha")
            ]);

        }

        if(empty($json->success)) {

            throw ValidationException::withMessages([
                "captcha" => trans("auth.captcha")
            ]);

        }

        Auth::login($user, $this->boolean("remember"));

        RateLimiter::clear($this->throttleKey());

    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void {

        if(!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            "email" => trans("auth.throttle", [
                "seconds" => $seconds,
                "minutes" => ceil($seconds / 60),
            ]),
        ]);

    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string {

        return Str::transliterate(Str::lower($this->input("email"))."|".$this->ip());

    }

}
