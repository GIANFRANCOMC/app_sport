<?php

declare(strict_types=1);

namespace App\Models\System\Devices;

use Illuminate\Database\Eloquent\Model;
use App\Models\System\Organizations\{Company, Branch};

class BiometricDevice extends Model {

    protected $table               = "biometric_devices";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $fillable = [
        "company_id",
        "branch_id",
        "biometric_device_model_id",
        "name",
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
        "formatted_status",
        "brand_name",
        "model_name"
    ];

    public function getFormattedStatusAttribute(): string {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public function getBrandNameAttribute(): string {

        return $this->model?->brand?->name ?? "";

    }

    public function getModelNameAttribute(): string {

        return $this->model?->name ?? "";

    }

    public static function getStatuses(string $type = "all", ?string $code = ""): array {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return \App\Helpers\System\Utilities::getValues($statuses, $type, $code);

    }

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

    public function model() {

        return $this->belongsTo(BiometricDeviceModel::class, "biometric_device_model_id", "id");

    }

    public function customerFingerprints() {

        return $this->hasMany(CustomerBiometricFingerprint::class, "biometric_device_id", "id");

    }

}
