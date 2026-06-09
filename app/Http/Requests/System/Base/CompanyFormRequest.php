<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Base;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared request contract for company-scoped System modules.
 */
abstract class CompanyFormRequest extends FormRequest {

    public function authorize(): bool {

        return (int) ($this->user()?->company_id ?? 0) > 0;

    }

    protected function prepareForValidation(): void {

        $normalized = [];

        foreach($this->normalizedStringFields() as $field) {

            if(!$this->exists($field)) {

                continue;

            }

            $value = $this->input($field);

            if(!is_string($value)) {

                continue;

            }

            $value = trim($value);
            $normalized[$field] = $value === "" ? null : $value;

        }

        if($normalized !== []) {

            $this->merge($normalized);

        }

    }

    /**
     * @return array<int, string>
     */
    protected function normalizedStringFields(): array {

        return [];

    }

}
