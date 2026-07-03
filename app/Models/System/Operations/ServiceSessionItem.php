<?php

declare(strict_types=1);

namespace App\Models\System\Operations;

use App\Models\System\Catalogs\Item;
use App\Models\System\Organizations\User;
use Illuminate\Database\Eloquent\Model;

final class ServiceSessionItem extends Model {

    protected $table = "service_session_items";

    protected $fillable = [
        "company_id",
        "service_session_id",
        "item_id",
        "assigned_user_id",
        "name",
        "item_type",
        "quantity",
        "unit_price",
        "status",
        "started_at",
        "ended_at",
        "duration_minutes",
        "observation",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "quantity" => "decimal:4",
        "unit_price" => "decimal:4",
        "started_at" => "datetime",
        "ended_at" => "datetime",
        "duration_minutes" => "integer"
    ];

    public function session() {

        return $this->belongsTo(ServiceSession::class, "service_session_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

    public function assignedUser() {

        return $this->belongsTo(User::class, "assigned_user_id", "id");

    }

}
