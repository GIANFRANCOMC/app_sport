<?php

declare(strict_types=1);

namespace App\Models\System\Warehouses;

use App\Models\System\Organizations\{Company, User};
use Illuminate\Database\Eloquent\Model;

final class InventoryGuide extends Model {

    protected $fillable = [
        "company_id",
        "warehouse_id",
        "number",
        "guide_type",
        "issue_date",
        "reason",
        "reference",
        "status",
        "confirmed_at",
        "confirmed_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "issue_date" => "date:Y-m-d",
        "confirmed_at" => "datetime",
        "canceled_at" => "datetime"
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function items() { return $this->hasMany(InventoryGuideItem::class); }
    public function confirmedBy() { return $this->belongsTo(User::class, "confirmed_by"); }
}
