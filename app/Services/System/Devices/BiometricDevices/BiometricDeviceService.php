<?php

declare(strict_types=1);

namespace App\Services\System\Devices\BiometricDevices;

use DomainException;
use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Models\System\Customers\{Customer};
use App\Models\System\Devices\{BiometricDevice, CustomerBiometricFingerprint, UserBiometricFingerprint};
use App\Models\System\Organizations\User;

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class BiometricDeviceService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Devices.biometric_device";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
        "branch_id",
        "biometric_device_model_id",
        "name",
        "serial_number",
        "ip_address",
        "port",
        "device_id",
        "description",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "name",
        "serial_number",
        "ip_address",
        "port"
    ];

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Prepare data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company
     * @param int $userId User
     * @return array
     */
    private static function prepareBiometricDeviceDataForCreate(array $data, int $companyId, int $userId): array {

        $deviceData = [
            "company_id" => $companyId,
            "status"     => $data["status"] ?? "active",
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
     * Prepare data for update (only changed fields)
     *
     * @param BiometricDevice $device Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareBiometricDeviceDataForUpdate(BiometricDevice $device, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field]) && $data[$field] !== $device->$field) {

                $updateData[$field] = $data[$field];

            }

        }

        return $updateData;

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return BiometricDevice|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?BiometricDevice {

        $device = null;

        DB::transaction(function() use($data, $userId, &$device) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Prepare data with only allowed fields
            $deviceData = self::prepareBiometricDeviceDataForCreate($data, $companyId, $userId);

            // Create the record
            $device = BiometricDevice::create($deviceData);

        });

        return $device;

    }

    /**
     * Update an existing record
     *
     * @param BiometricDevice $device Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return BiometricDevice Updated record instance
     */
    public static function update(BiometricDevice $device, array $data, ?int $userId = null): BiometricDevice {

        DB::transaction(function() use($device, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Prepare update data with only changed fields
            $updateData = self::prepareBiometricDeviceDataForUpdate($device, $data);

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $device->update($updateData);

            }

        });

        return $device->fresh(["branch", "model.brand"]);

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param array|null $statuses Filter by statuses (e.g. ["active"], ["active", "inactive"])
     * @param array $relations Relations to eager load
     * @return BiometricDevice|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, ?array $statuses = ["active"], array $relations = ["branch", "model.brand"]): ?BiometricDevice {

        $query = BiometricDevice::where("id", $id)
                                ->where("company_id", $companyId);

        if($statuses !== null && !empty($statuses)) {

            $query->whereIn("status", $statuses);

        }

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get paginated list of records with filters
     *
     * @param int $companyId Company
     * @param array $filters Filter parameters (filter_by, word)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $query = BiometricDevice::where("company_id", $companyId)
                                ->with(["branch", "model.brand"]);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = "%{$word}%";

            if($filterBy === "all") {

                // Search across all searchable fields
                $query->where(function(Builder $q) use($searchTerm) {

                    $searchableFields = self::SEARCHABLE_FIELDS;
                    $firstField       = array_shift($searchableFields);

                    if($firstField) {

                        $q->where($firstField, "like", $searchTerm);

                    }

                    foreach($searchableFields as $field) {

                        $q->orWhere($field, "like", $searchTerm);

                    }

                });

            }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

                // Search in specific field
                $query->where($filterBy, "like", $searchTerm);

            }

        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);

    }

    /**
     * Find record by IP and company
     *
     * @param string $ipAddress IP
     * @param int $companyId Company
     * @return BiometricDevice|null
     */
    public static function findByIpAndCompany(string $ipAddress, int $companyId): ?BiometricDevice {

        return BiometricDevice::where("ip_address", $ipAddress)
                              ->where("company_id", $companyId)
                              ->where("status", "active")
                              ->first();

    }

    /**
     * Get all active records for a company
     *
     * @param int $companyId Company
     * @param int|null $branchId Branch (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveDevices(int $companyId, ?int $branchId = null) {

        $query = BiometricDevice::where("company_id", $companyId)
                                ->where("status", "active");

        if(Utilities::isDefined($branchId)) {

            $query->where("branch_id", $branchId);

        }

        return $query->with(["branch", "model.brand"])->get();

    }

    /**
     * Register customer fingerprint in device
     *
     * @param int $customerId Customer
     * @param int $biometricDeviceId Device
     * @param int $deviceUserId User in the device
     * @param int $fingerIndex Finger index (0-9)
     * @param int $userId User who creates the record
     * @param int $companyId Company
     * @return CustomerBiometricFingerprint
     */
    public static function registerFingerprint(int $customerId, int $biometricDeviceId, int $deviceUserId, int $fingerIndex = 0, int $userId = 0, int $companyId = 0): CustomerBiometricFingerprint {

        return DB::transaction(function() use($customerId, $biometricDeviceId, $deviceUserId, $fingerIndex, $userId, $companyId) {
            self::lockFingerprintDevice($biometricDeviceId, $companyId);

            if(!Customer::query()->where("company_id", $companyId)->where("status", "active")->whereKey($customerId)->exists()) {
                throw new DomainException("El cliente no está activo o no pertenece a la empresa.");
            }

            self::assertAvailableDeviceUserId($biometricDeviceId, $deviceUserId, $fingerIndex);

            return CustomerBiometricFingerprint::create([
                "company_id"          => $companyId,
                "customer_id"         => $customerId,
                "biometric_device_id" => $biometricDeviceId,
                "device_user_id"      => $deviceUserId,
                "finger_index"        => $fingerIndex,
                "status"              => "active",
                "created_at"          => now(),
                "created_by"          => $userId
            ]);
        });

    }

    /**
     * Find customer by device user
     *
     * @param int $deviceId Device
     * @param int $deviceUserId User in device
     * @param int $companyId Company
     * @return Customer|null
     */
    public static function findCustomerByDeviceUserId(int $deviceId, int $deviceUserId, int $companyId): ?Customer {

        $fingerprint = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
                                                   ->where("device_user_id", $deviceUserId)
                                                   ->where("company_id", $companyId)
                                                   ->where("status", "active")
                                                   ->with("customer")
                                                   ->first();

        return $fingerprint?->customer;

    }

    public static function registerUserFingerprint(
        int $employeeUserId,
        int $biometricDeviceId,
        int $deviceUserId,
        int $fingerIndex = 0,
        int $actorId = 0,
        int $companyId = 0
    ): UserBiometricFingerprint {

        return DB::transaction(function() use($employeeUserId, $biometricDeviceId, $deviceUserId, $fingerIndex, $actorId, $companyId) {
            self::lockFingerprintDevice($biometricDeviceId, $companyId);

            if(!User::query()->where("company_id", $companyId)->where("status", "active")->whereKey($employeeUserId)->exists()) {
                throw new DomainException("El colaborador no está activo o no pertenece a la empresa.");
            }

            self::assertAvailableDeviceUserId($biometricDeviceId, $deviceUserId, $fingerIndex);

            return UserBiometricFingerprint::create([
                "company_id" => $companyId,
                "user_id" => $employeeUserId,
                "biometric_device_id" => $biometricDeviceId,
                "device_user_id" => $deviceUserId,
                "finger_index" => $fingerIndex,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $actorId
            ]);
        });

    }

    public static function findUserByDeviceUserId(int $deviceId, int $deviceUserId, int $companyId): ?User {

        $fingerprint = UserBiometricFingerprint::query()
            ->where("biometric_device_id", $deviceId)
            ->where("device_user_id", $deviceUserId)
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->whereHas("user", function(Builder $query) use($companyId) {
                $query->where("company_id", $companyId)->where("status", "active");
            })
            ->with("user")
            ->first();

        return $fingerprint?->user;

    }

    /**
     * Get next available device user for a device
     *
     * @param int $deviceId Device
     * @return int
     */
    public static function getNextDeviceUserId(int $deviceId): int {

        $customerMax = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
            ->max("device_user_id");
        $userMax = UserBiometricFingerprint::where("biometric_device_id", $deviceId)
            ->max("device_user_id");
        $maxUserId = max((int) ($customerMax ?? 0), (int) ($userMax ?? 0));

        return ($maxUserId ?? 0) + 1;

    }

    /**
     * Check if device user and finger index combination already exists
     *
     * @param int $deviceId Device
     * @param int $deviceUserId User in device
     * @param int $fingerIndex Finger index (optional, to check specific finger)
     * @return bool
     */
    public static function deviceUserIdExists(int $deviceId, int $deviceUserId, ?int $fingerIndex = null): bool {

        $customerQuery = CustomerBiometricFingerprint::where("biometric_device_id", $deviceId)
            ->where("device_user_id", $deviceUserId);
        $userQuery = UserBiometricFingerprint::where("biometric_device_id", $deviceId)
            ->where("device_user_id", $deviceUserId);

        if(Utilities::isDefined($fingerIndex)) {

            $customerQuery->where("finger_index", $fingerIndex);
            $userQuery->where("finger_index", $fingerIndex);

        }

        return $customerQuery->exists() || $userQuery->exists();

    }

    private static function lockFingerprintDevice(int $deviceId, int $companyId): BiometricDevice {

        $device = BiometricDevice::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->lockForUpdate()
            ->find($deviceId);

        if(!$device) {
            throw new DomainException("El dispositivo biométrico no está disponible.");
        }

        return $device;

    }

    private static function assertAvailableDeviceUserId(int $deviceId, int $deviceUserId, int $fingerIndex): void {

        if(self::deviceUserIdExists($deviceId, $deviceUserId, $fingerIndex)) {
            throw new DomainException("El identificador biométrico ya está utilizado en este dispositivo.");
        }

    }

}
