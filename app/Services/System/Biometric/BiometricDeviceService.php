<?php

declare(strict_types=1);

namespace App\Services\System\Biometric;

use App\Helpers\System\{TranslationHelper, Utilities};
use App\Models\System\Biometric\BiometricDevice;
use App\Models\System\Biometric\CustomerBiometricFingerprint;
use App\Models\System\Customers\Customer;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Exception;

/**
 * Service class for managing Biometric Device operations
 * Handles business logic for creating and updating biometric devices
 */
class BiometricDeviceService
{
    /**
     * Translation namespace for biometric device module
     */
    private const TRANSLATION_NAMESPACE = "System.Biometric.biometric_device";

    /**
     * Allowed fields for biometric device creation and update
     */
    private const ALLOWED_FIELDS = [
        "branch_id",
        "name",
        "brand",
        "model",
        "serial_number",
        "ip_address",
        "port",
        "device_id",
        "description",
        "status"
    ];

    /**
     * Find biometric device by IP and company
     *
     * @param string $ipAddress IP address
     * @param int $companyId Company ID
     * @return BiometricDevice|null
     */
    public static function findByIpAndCompany(string $ipAddress, int $companyId): ?BiometricDevice
    {
        return BiometricDevice::where("ip_address", $ipAddress)
                             ->where("company_id", $companyId)
                             ->where("status", "active")
                             ->first();
    }

    /**
     * Get all active devices for a company
     *
     * @param int $companyId Company ID
     * @param int|null $branchId Branch ID (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveDevices(int $companyId, ?int $branchId = null)
    {
        $query = BiometricDevice::where("company_id", $companyId)
                               ->where("status", "active");

        if (Utilities::isDefined($branchId)) {
            $query->where("branch_id", $branchId);
        }

        return $query->with(["branch"])->get();
    }

    /**
     * Register customer fingerprint in device
     *
     * @param int $customerId Customer ID
     * @param int $biometricDeviceId Device ID
     * @param int $deviceUserId User ID in the device
     * @param int $fingerIndex Finger index (0-9)
     * @param int $userId User ID who creates the record
     * @param int $companyId Company ID
     * @return CustomerBiometricFingerprint
     */
    public static function registerFingerprint(
        int $customerId,
        int $biometricDeviceId,
        int $deviceUserId,
        int $fingerIndex = 0,
        int $userId = 0,
        int $companyId = 0
    ): CustomerBiometricFingerprint {
        return CustomerBiometricFingerprint::create([
            "company_id" => $companyId,
            "customer_id" => $customerId,
            "biometric_device_id" => $biometricDeviceId,
            "device_user_id" => $deviceUserId,
            "finger_index" => $fingerIndex,
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ]);
    }

    /**
     * Find customer by device user ID
     *
     * @param int $deviceId Device ID
     * @param int $deviceUserId User ID in device
     * @param int $companyId Company ID
     * @return Customer|null
     */
    public static function findCustomerByDeviceUserId(
        int $deviceId,
        int $deviceUserId,
        int $companyId
    ): ?Customer {
        $fingerprint = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
                                                   ->where("device_user_id", $deviceUserId)
                                                   ->where("company_id", $companyId)
                                                   ->where("status", "active")
                                                   ->with("customer")
                                                   ->first();

        return $fingerprint?->customer;
    }

    /**
     * Get next available device user ID for a device
     *
     * @param int $deviceId Device ID
     * @return int
     */
    public static function getNextDeviceUserId(int $deviceId): int
    {
        $maxUserId = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
                                                 ->max("device_user_id");

        return ($maxUserId ?? 0) + 1;
    }

    /**
     * Check if device user ID and finger index combination already exists
     *
     * @param int $deviceId Device ID
     * @param int $deviceUserId User ID in device
     * @param int $fingerIndex Finger index (optional, to check specific finger)
     * @return bool
     */
    public static function deviceUserIdExists(int $deviceId, int $deviceUserId, ?int $fingerIndex = null): bool
    {
        $query = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
                                             ->where("device_user_id", $deviceUserId);

        if (Utilities::isDefined($fingerIndex)) {
            $query->where("finger_index", $fingerIndex);
        }

        return $query->exists();
    }

    /**
     * Get paginated list of biometric devices with filters
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters (filter_by, word)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = BiometricDevice::where("company_id", $companyId)
                                ->with(["branch"]);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word = $filters["word"] ?? null;

        if (Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {
            if ($filterBy === "name") {
                $query->where("name", "like", "%{$word}%");
            } elseif ($filterBy === "ip_port") {
                // Search in both IP address and port
                $query->where(function (Builder $q) use ($word) {
                    $q->where("ip_address", "like", "%{$word}%")
                      ->orWhere("port", "like", "%{$word}%");
                });
            }
        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);
    }

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string
    {
        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);
    }

    /**
     * Prepare biometric device data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @return array
     */
    private static function prepareBiometricDeviceDataForCreate(array $data, int $companyId, int $userId): array
    {
        $deviceData = [
            "company_id" => $companyId,
            "status" => $data["status"] ?? "active",
            "port" => $data["port"] ?? 4370,
            "brand" => $data["brand"] ?? "ZKTeco",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {
            if(isset($data[$field])) {
                $deviceData[$field] = $data[$field];
            }
        }

        return $deviceData;
    }

    /**
     * Prepare biometric device data for update (only changed fields)
     *
     * @param BiometricDevice $device Device instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareBiometricDeviceDataForUpdate(BiometricDevice $device, array $data): array
    {
        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {
            if(isset($data[$field]) && $data[$field] !== $device->$field) {
                $updateData[$field] = $data[$field];
            }
        }

        return $updateData;
    }

    /**
     * Create a new biometric device
     *
     * @param array $data Device data from request
     * @param int|null $userId User ID creating the device
     * @return BiometricDevice|null Created device instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?BiometricDevice
    {
        $device = null;

        DB::transaction(function() use($data, $userId, &$device) {
            $userAuth = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {
                throw new Exception(self::trans("company_id_required"));
            }

            $userId = $userId ?? $userAuth->id;

            // Prepare device data with only allowed fields
            $deviceData = self::prepareBiometricDeviceDataForCreate($data, $companyId, $userId);

            // Create the device
            $device = BiometricDevice::create($deviceData);
        });

        return $device;
    }

    /**
     * Update an existing biometric device
     *
     * @param BiometricDevice $device Device instance to update
     * @param array $data Updated device data
     * @param int|null $userId User ID updating the device
     * @return BiometricDevice Updated device instance
     */
    public static function update(BiometricDevice $device, array $data, ?int $userId = null): BiometricDevice
    {
        DB::transaction(function() use($device, $data, $userId) {
            $userAuth = Auth::user();
            $userId = $userId ?? $userAuth->id;

            // Prepare update data with only changed fields
            $updateData = self::prepareBiometricDeviceDataForUpdate($device, $data);

            // Only update if there are changes
            if(!empty($updateData)) {
                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $device->update($updateData);
            }
        });

        return $device->fresh(["branch"]);
    }

    /**
     * Find biometric device by ID and company ID
     *
     * @param int $id Device ID
     * @param int $companyId Company ID
     * @param bool $activeOnly Only search active devices
     * @param array $relations Relations to eager load
     * @return BiometricDevice|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, bool $activeOnly = false, array $relations = ["branch"]): ?BiometricDevice
    {
        $query = BiometricDevice::where("id", $id)
                               ->where("company_id", $companyId);

        if($activeOnly) {
            $query->where("status", "active");
        }

        if(!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

