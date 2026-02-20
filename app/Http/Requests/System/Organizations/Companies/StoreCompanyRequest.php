<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Companies;

use App\Helpers\System\Utilities;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\System\Defaults\{DocumentNumberLength};

class StoreCompanyRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {

        return false;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $validations = [];

        return $validations;

    }


}
