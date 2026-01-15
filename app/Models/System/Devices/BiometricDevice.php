<?php

declare(strict_types=1);

namespace App\Models\System\Devices;

use Illuminate\Database\Eloquent\Model;
use App\Models\System\Organizations\{Company, Branch};
use App\Models\System\Customers\Customer;

class BiometricDevice extends Model
{
    protected $table               = "biometric_devices";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $fillable = [
        "company_id",
        "branch_id",
        "name",
        "brand",
        "model",
        "serial_number",
        "ip_address",
        "port",
        "device_id",
        "description",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $appends = [
        "formatted_status"
    ];

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return self::getStatuses("first", $this->attributes["status"])["label"] ?? "";
    }

    /**
     * Get statuses list
     */
    public static function getStatuses(string $type = "all", ?string $code = ""): array
    {
        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return \App\Helpers\System\Utilities::getValues($statuses, $type, $code);
    }

    /**
     * Get brands list
     */
    public static function getBrands(string $type = "all", ?string $code = ""): array
    {
        $brands = [
            ["code" => "ZKTeco", "label" => "ZKTeco"]
        ];

        return \App\Helpers\System\Utilities::getValues($brands, $type, $code);
    }

    /**
     * Get models by brand
     */
    public static function getModelsByBrand(?string $brand = "ZKTeco"): array
    {
        $models = [
            "ZKTeco" => [
                ["code" => "K20 Pro", "label" => "K20 Pro"]
            ]
        ];

        return $models[$brand] ?? [];
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, "company_id", "id");
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, "branch_id", "id");
    }

    public function customerFingerprints()
    {
        return $this->hasMany(CustomerBiometricFingerprint::class, "biometric_device_id", "id");
    }
}

