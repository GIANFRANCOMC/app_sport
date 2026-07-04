<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\TrackingAttendances;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAttendanceCorrectionRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "start_date" => "nullable|date",
            "end_date" => "nullable|date|after:start_date",
            "reason" => "required|string|max:500"
        ];

    }

}
