<?php

declare(strict_types=1);

namespace App\Rules\System\Defaults;

use Closure;
use Illuminate\Contracts\Validation\{ValidationRule};
use Illuminate\Support\Facades\{Auth, DB};

/**
 * Validation rule to verify that a field value is unique within a table for a specific company_id
 */
class UniqueInCompany implements ValidationRule {
    /**
     * Table name
     */
    private string $table;

    /**
     * Field name to check uniqueness
     */
    private string $field;

    /**
     * ID to exclude from validation (useful for update operations)
     */
    private ?int $excludeId;

    /**
     * Extra where clauses (e.g. ["type" => "product"])
     *
     * @var array<string, mixed>
     */
    private array $extraWhere;

    /**
     * Custom attribute name for error messages
     */
    private ?string $attributeName;

    /**
     * Constructor
     *
     * @param  string  $table Table name
     * @param  string  $field Field name to check uniqueness
     * @param  int|null  $excludeId ID to exclude from validation
     * @param  array<string, mixed>  $extraWhere Extra where clauses to apply
     * @param  string|null  $attributeName Custom attribute name for error messages
     */
    public function __construct(string $table, string $field, ?int $excludeId = null, array $extraWhere = [], ?string $attributeName = null) {

        $this->table = $table;
        $this->field = $field;
        $this->excludeId = $excludeId;
        $this->extraWhere = $extraWhere;
        $this->attributeName = $attributeName;

    }

    /**
     * Run the validation rule.
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

        foreach($this->extraWhere as $field => $extraValue) {

            $query->where((string) $field, $extraValue);

        }

        if($this->excludeId !== null) {

            $query->where("id", "!=", $this->excludeId);

        }

        if($query->exists()) {

            $fieldName = $this->attributeName ?? $attribute;
            $fail("El campo {$fieldName} ya está en uso.");

        }

    }
}
