<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\TrackingAttendances;

use App\Http\Requests\System\Base\BaseFormRequest;

class CancelTrackingAttendanceRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        return [
            "motive" => "nullable|string|max:300"
        ];

    }

}
