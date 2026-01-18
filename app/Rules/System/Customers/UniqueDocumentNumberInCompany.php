<?php

declare(strict_types=1);

namespace App\Rules\System\Customers;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\{Auth, DB};

/**
 * Validation rule to verify that document_number is unique within customers table for a specific company_id
 */
class UniqueDocumentNumberInCompany implements ValidationRule {

    /**
     * ID to exclude from validation (useful for update operations)
     *
     * @var int|null
     */
    private ?int $excludeId;

    /**
     * Custom attribute name for error messages
     *
     * @var string|null
     */
    private ?string $attributeName;

    /**
     * Constructor
     *
     * @param int|null $excludeId ID to exclude from validation
     * @param string|null $attributeName Custom attribute name for error messages
     */
    public function __construct(?int $excludeId = null, ?string $attributeName = null) {

        $this->excludeId     = $excludeId;
        $this->attributeName = $attributeName;

    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {

        $user = Auth::user();

        if(!$user || !$user->company_id) {

            $fail("No se pudo validar la unicidad del campo.");
            return;

        }

        $query = DB::table("customers")
                   ->where("document_number", $value)
                   ->where("company_id", $user->company_id);

        if($this->excludeId) {

            $query->where("id", "!=", $this->excludeId);

        }

        if($query->exists()) {

            $fieldName = $this->attributeName ?? $attribute;
            $fail("El campo {$fieldName} ya está en uso para esta empresa.");

        }

    }

}

