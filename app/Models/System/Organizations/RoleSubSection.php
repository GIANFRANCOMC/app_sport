<?php

namespace App\Models\System\Organizations;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\General\SubSection;

class RoleSubSection extends Model {

    protected $table = "role_sub_sections";

    protected $fillable = [
        "role_id",
        "sub_section_id",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    public function role() {

        return $this->belongsTo(Role::class, "role_id", "id");

    }

    public function subSection() {

        return $this->belongsTo(SubSection::class, "sub_section_id", "id");

    }

}
