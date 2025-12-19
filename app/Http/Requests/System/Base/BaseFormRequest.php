<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Base;

use App\Helpers\System\{ApiResponse, Utilities};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base Form Request
 * Provides common functionality for all form requests
 */
abstract class BaseFormRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {

        return true;

    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void {

        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(
            ApiResponse::validationError($errors, Utilities::$messages["422"] ?? "Validation failed")
        );

    }

}

