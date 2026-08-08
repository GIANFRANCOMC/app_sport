<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\UserAttendances;

use App\Http\Requests\System\Base\{CompanyFormRequest};
use App\Rules\System\Defaults\{BelongsToCompany};

final class CheckOutUserAttendanceRequest extends CompanyFormRequest {
    public function rules(): array {

        return [
            "branch_id" => [
                "required",
                "integer",
                new BelongsToCompany("branches", ["status" => "active"], "La sucursal no está disponible."),
            ],
            "user_id" => [
                "required",
                "integer",
                new BelongsToCompany("users", ["status" => "active"], "El colaborador no está disponible."),
            ],
            "checked_out_at" => ["nullable", "date"],
        ];

    }

    public function attributes(): array {

        return [
            "branch_id" => "sucursal",
            "user_id" => "colaborador",
            "checked_out_at" => "fecha y hora de salida",
        ];

    }
}
