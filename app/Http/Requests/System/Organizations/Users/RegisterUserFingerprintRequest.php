<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Organizations\Users;

use App\Http\Requests\System\Base\CompanyFormRequest;
use App\Rules\System\Defaults\BelongsToCompany;

final class RegisterUserFingerprintRequest extends CompanyFormRequest {

    public function rules(): array {

        return [
            "biometric_device_id" => [
                "bail",
                "required",
                "integer",
                new BelongsToCompany("biometric_devices", ["status" => "active"], "El dispositivo biometrico no esta disponible.")
            ],
            "device_user_id" => ["nullable", "integer", "min:1"],
            "finger_index" => ["nullable", "integer", "min:0", "max:9"]
        ];

    }

}
