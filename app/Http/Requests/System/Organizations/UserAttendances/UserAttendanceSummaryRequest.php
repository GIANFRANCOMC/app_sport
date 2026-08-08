<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\UserAttendances;

use App\Http\Requests\System\Base\{CompanyFormRequest};
use App\Rules\System\Defaults\{BelongsToCompany};

final class UserAttendanceSummaryRequest extends CompanyFormRequest {
    public function rules(): array {

        return [
            "user_id" => ["required", "integer", new BelongsToCompany("users")],
            "branch_id" => ["nullable", "integer", new BelongsToCompany("branches")],
            "week_start" => ["nullable", "date"],
        ];

    }
}
