<?php

declare(strict_types=1);

namespace App\Models\System\Operations;

use App\Models\System\Organizations\Branch;
use App\Models\System\Organizations\Company;
use Illuminate\Database\Eloquent\Model;

final class ServiceFloor extends Model {
    protected $table = "service_floors";

    protected $fillable = [
        "company_id",
        "branch_id",
        "code",
        "name",
        "level_number",
        "sort_order",
        "background_color",
        "description",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "level_number" => "integer",
        "sort_order" => "integer",
    ];

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

    public function stations() {

        return $this->hasMany(ServiceStation::class, "service_floor_id", "id");

    }
}
