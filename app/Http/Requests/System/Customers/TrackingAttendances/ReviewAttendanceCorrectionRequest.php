<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Customers\TrackingAttendances;

use Illuminate\Foundation\Http\FormRequest;

final class ReviewAttendanceCorrectionRequest extends FormRequest {

    public function authorize(): bool {

        return true;

    }

    public function rules(): array {

        return [
            "decision" => "required|string|in:approved,rejected",
            "note" => "nullable|string|max:500"
        ];

    }

}
