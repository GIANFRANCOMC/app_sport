<?php

declare(strict_types=1);

namespace App\Models\System\Organizations;

use Illuminate\Database\Eloquent\Model;

final class UserAttendance extends Model {

    protected $table = "user_attendances";

    protected $fillable = [
        "company_id",
        "branch_id",
        "user_id",
        "work_date",
        "checked_in_at",
        "checked_out_at",
        "worked_minutes",
        "source_type",
        "source_reference",
        "observation",
        "motive",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "work_date" => "date",
        "checked_in_at" => "datetime",
        "checked_out_at" => "datetime",
        "worked_minutes" => "integer"
    ];

    protected $appends = [
        "worked_hours"
    ];

    public function getWorkedHoursAttribute(): float {

        return round(((int) ($this->attributes["worked_minutes"] ?? 0)) / 60, 2);

    }

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

    public function user() {

        return $this->belongsTo(User::class, "user_id", "id");

    }

}
