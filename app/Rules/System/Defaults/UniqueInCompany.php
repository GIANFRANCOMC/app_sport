<?php

declare(strict_types=1);

namespace App\Rules\System\Defaults;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\{Auth, DB};

/**
 * Validation rule to verify that a field value is unique within a table for a specific company_id
 */
class UniqueInCompany implements ValidationRule {

    /**
     * Table name
     *
     * @var string
     */
    private string $table;

    /**
     * Field name to check uniqueness
     *
     * @var string
     */
    private string $field;

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
     * @param string $table Table name
     * @param string $field Field name to check uniqueness
     * @param int|null $excludeId ID to exclude from validation
     * @param string|null $attributeName Custom attribute name for error messages
     */
    public function __construct(string $table, string $field, ?int $excludeId = null, ?string $attributeName = null) {

        $this->table         = $table;
        $this->field         = $field;
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

        $query = DB::table($this->table)
                   ->where($this->field, $value)
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

