<?php

declare(strict_types=1);

namespace App\Rules\System\Defaults;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\{Auth, DB};

/**
 * Validation rule to verify that a record belongs to the company of the authenticated user
 */
class BelongsToCompany implements ValidationRule {

    /**
     * Table name
     *
     * @var string
     */
    private string $table;

    /**
     * Extra where clauses (e.g. ["type" => "product", "status" => "active"])
     *
     * @var array<string, mixed>
     */
    private array $extraWhere;

    /**
     * Custom error message
     *
     * @var string|null
     */
    private ?string $customMessage;

    /**
     * @var array<int, array{0:string, 1:string, 2:string, 3:string}>
     */
    private array $joins;

    private string $companyColumn;

    private string $keyColumn;

    /**
     * Constructor
     *
     * @param string $table Table name
     * @param array<string, mixed> $extraWhere Extra where clauses to apply
     * @param string|null $customMessage Custom error message
     */
    public function __construct(
        string $table,
        array $extraWhere = [],
        ?string $customMessage = null,
        array $joins = [],
        string $companyColumn = "company_id",
        string $keyColumn = "id"
    ) {

        $this->table         = $table;
        $this->extraWhere    = $extraWhere;
        $this->customMessage = $customMessage;
        $this->joins         = $joins;
        $this->companyColumn = $companyColumn;
        $this->keyColumn     = $keyColumn;

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

            $message = $this->customMessage ?? "El registro seleccionado no pertenece a su empresa.";
            $fail($message);
            return;

        }

        $query = DB::table($this->table);

        foreach($this->joins as $join) {

            if(count($join) !== 4) {

                throw new \InvalidArgumentException("Company ownership joins require table, first column, operator and second column.");

            }

            $query->join($join[0], $join[1], $join[2], $join[3]);

        }

        $query->where($this->keyColumn, $value)
              ->where($this->companyColumn, $user->company_id);

        foreach($this->extraWhere as $field => $extraValue) {

            $query->where((string)$field, $extraValue);

        }

        if(!$query->exists()) {

            $message = $this->customMessage ?? "El registro seleccionado no pertenece a su empresa.";
            $fail($message);

        }

    }

}

