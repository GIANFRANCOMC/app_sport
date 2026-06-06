<?php

declare(strict_types=1);

namespace App\Http\Requests\System\Essentials\Home;

use App\Models\System\Organizations\CompanySubSection;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHomePreferenceRequest extends FormRequest {

    public function authorize(): bool {

        $user = $this->user();
        $subSectionId = (int) $this->route("id");

        if(!$user) {

            return false;

        }

        if($subSectionId === 0) {

            return true;

        }

        return CompanySubSection::where("company_id", $user->company_id)
                                ->where("sub_section_id", $subSectionId)
                                ->where("status", "active")
                                ->whereHas("subSection", function($query) {

                                    $query->where("status", "active");

                                })
                                ->exists();

    }

    public function rules(): array {

        return [
            "show_actions"        => ["required", "boolean"],
            "show_only_favorites" => ["required", "boolean"],
            "is_favorite"         => ["sometimes", "boolean"]
        ];

    }

}
