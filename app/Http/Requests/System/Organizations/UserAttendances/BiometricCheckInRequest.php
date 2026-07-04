<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\UserAttendances;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

final class BiometricCheckInRequest extends CompanyFormRequest {

    public function rules(): array {

        return [
            "branch_id" => ["required", "integer", new BelongsToCompany("branches", ["status" => "active"])],
            "device_id" => ["required", "integer", new BelongsToCompany("biometric_devices", ["status" => "active"])],
            "device_user_id" => ["required", "integer", "min:1"],
            "checked_in_at" => ["nullable", "date"]
        ];

    }

}
