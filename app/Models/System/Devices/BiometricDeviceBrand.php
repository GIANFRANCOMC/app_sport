<?php

declare(strict_types=1);

namespace App\Models\System\Devices;

use Illuminate\Database\Eloquent\Model;
use App\Models\System\Organizations\Company;

final class BiometricDeviceBrand extends Model {

    protected $table = "biometric_device_brands";

    protected $fillable = [
        "company_id",
        "slug",
        "name",
        "description",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function models() {

        return $this->hasMany(BiometricDeviceModel::class, "biometric_device_brand_id", "id");

    }

}
