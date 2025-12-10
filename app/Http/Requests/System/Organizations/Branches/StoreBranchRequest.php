<?php

namespace App\Http\Requests\System\Organizations\Branches;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {

        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $userAuth = Auth::user();

        return [
            "internal_code" => ["required", "string", "max:50", Rule::unique("branches", "internal_code")->where("company_id", $userAuth->company_id)],
            "name"          => "required|string|max:100",
            "address"       => "nullable|string|max:100",
            "reference"     => "nullable|string|max:150",
            "telephone"     => "nullable|string|max:25",
            "email"         => "nullable|email|max:120",
            "capacity"      => "nullable|integer|min:0",
            "map_url"       => "nullable|url|max:255",
            "status"        => "required|string|in:active,inactive"
        ];

    }

    protected function failedValidation(Validator $validator) {

        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json(["errors" => $errors, "message" => Utilities::$messages["422"]], 422));

    }

}
