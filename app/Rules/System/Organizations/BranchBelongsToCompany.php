<?php

declare(strict_types=1);

namespace App\Rules\System\Organizations;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\{Auth, DB};

/**
 * Validation to verify that a branch_id belongs to the company of the authenticated user
 */
class BranchBelongsToCompany implements ValidationRule {

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

            $fail("La sucursal seleccionada no pertenece a su empresa.");
            return;

        }

        $exists = DB::table("branches")
                    ->where("id", $value)
                    ->where("company_id", $user->company_id)
                    ->exists();

        if(!$exists) {

            $fail("La sucursal seleccionada no pertenece a su empresa.");

        }

    }

}

