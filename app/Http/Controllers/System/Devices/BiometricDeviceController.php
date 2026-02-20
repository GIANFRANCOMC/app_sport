<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Devices;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Devices\BiometricDevices\{StoreBiometricDeviceRequest, UpdateBiometricDeviceRequest};
use App\Services\System\Devices\BiometricDevices\{BiometricDeviceConfigService, BiometricDeviceService};
use App\Services\System\Customers\Tracking\{TrackingAttendanceBusinessService};
use App\Models\System\Devices\{BiometricDevice};

class BiometricDeviceController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Devices.biometric_device";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return BiometricDeviceConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return BiometricDeviceService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Devices/biometric_devices/main");

    }

    /**
     * Show the form for creating a new record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created record
     *
     * @param StoreBiometricDeviceRequest $request
     * @return JsonResponse
     */
    public function store(StoreBiometricDeviceRequest $request): JsonResponse {

        try {

            $data   = $this->prepareBiometricDeviceData($request);
            $device = BiometricDeviceService::create($data, $this->getUserId());

            if(!Utilities::isDefined($device)) {

                return $this->errorResponse("create_failed");

            }

            BiometricDeviceConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($device, "created", "biometric_device");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return JsonResponse
     */
    public function show(BiometricDevice $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return void
     */
    public function edit(BiometricDevice $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateBiometricDeviceRequest $request
     * @param int $id Biometric Device
     * @return JsonResponse
     */
    public function update(UpdateBiometricDeviceRequest $request, int $id): JsonResponse {

        try {

            $device = BiometricDeviceService::findByIdAndCompany($id, $this->getCompanyId());

            if(!Utilities::isDefined($device)) {

                return $this->notFoundResponse();

            }

            $data   = $this->prepareBiometricDeviceData($request);
            $device = BiometricDeviceService::update($device, $data, $this->getUserId());

            if(!Utilities::isDefined($device)) {

                return $this->errorResponse("update_failed");

            }

            BiometricDeviceConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($device, "updated", "biometric_device");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return JsonResponse
     */
    public function destroy(BiometricDevice $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare record data from request
     *
     * @param StoreBiometricDeviceRequest|UpdateBiometricDeviceRequest $request
     * @return array
     */
    private function prepareBiometricDeviceData($request): array {

        return [
            "company_id"    => $this->getCompanyId(),
            "branch_id"     => $request->input("branch_id"),
            "name"          => $request->input("name"),
            "brand"         => $request->input("brand"),
            "model"         => $request->input("model"),
            "serial_number" => $request->input("serial_number"),
            "ip_address"    => $request->input("ip_address"),
            "port"          => $request->input("port"),
            "device_id"     => $request->input("device_id"),
            "description"   => $request->input("description"),
            "status"        => $request->input("status")
        ];

    }

    /**
     * Get translation namespace for module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    /**
     * Receive event from ZKTeco device
     * This endpoint is called by the ZKTeco device when a fingerprint is scanned
     *
     * @param Request $request
     * @param TrackingAttendanceBusinessService $businessService
     * @return JsonResponse
     */
    public function receiveEvent(Request $request, TrackingAttendanceBusinessService $businessService): JsonResponse {

        try {

            // Get company from request (set by middleware for guest routes)
            $company   = $request->get("company");
            $companyId = $company ? $company->id : ($this->getCompanyId() ?? 0);

            if(!$companyId) {

                return response()->json(["bool" => false, "msg" => $this->trans("company_not_identified")], 400);

            }

            // Validate required parameters
            $deviceUserId = $request->input("user_id"); // User ID from the device

            if(!Utilities::isDefined($deviceUserId)) {

                return response()->json(["bool" => false, "msg" => $this->trans("user_id_required")], 400);

            }

            // Get device identifier (IP address or device_id)
            $deviceIp  = $request->ip();
            $deviceId  = $request->input("device_id");
            $timestamp = $request->input("timestamp", now());
            $action    = $request->input("action", "checkin"); // "checkin" or "checkout"

            // Validate action
            if(!in_array($action, ["checkin", "checkout"])) {

                return response()->json(["bool" => false, "msg" => $this->trans("action_invalid")], 400);

            }

            // Find device by ID first (if provided), then by IP
            $device = null;

            if(Utilities::isDefined($deviceId)) {

                $device = BiometricDeviceService::findByIdAndCompany((int) $deviceId, $companyId, true, ["branch"]);

            }

            // If device not found by ID, try to find by IP
            if(!Utilities::isDefined($device)) {

                $device = BiometricDeviceService::findByIpAndCompany($deviceIp, $companyId);

            }

            if(!Utilities::isDefined($device)) {

                Log::warning("Biometric device not found", ["company_id" => $companyId, "device_id" => $deviceId, "device_ip" => $deviceIp, "request_ip" => $request->ip()]);

                return response()->json(["bool" => false, "msg" => $this->trans("device_not_found_or_unauthorized")], 404);

            }

            // Get customer by device user ID
            $customer = BiometricDeviceService::findCustomerByDeviceUserId($device->id, (int) $deviceUserId, $device->company_id);

            if(!Utilities::isDefined($customer)) {

                Log::warning("Biometric customer not found", ["device_id" => $device->id, "device_user_id" => $deviceUserId, "company_id" => $companyId]);

                return response()->json(["bool" => false, "msg" => $this->trans("user_not_found")], 404);

            }

            // Parse timestamp
            try {

                $attendanceDate = Carbon::parse($timestamp);

            }catch(\Exception $e) {

                Log::warning("Invalid timestamp format in biometric event", ["timestamp" => $timestamp, "device_id" => $device->id]);

                $attendanceDate = now();

            }

            // Process attendance
            $result = $businessService->validateAndCreateAttendance([
                "company_id" => $device->company_id,
                "branch_id" => $device->branch_id,
                "customer_id" => $customer->id,
                "device_id" => $device->id,
                "device_user_id" => (int) $deviceUserId,
                "start_date" => $attendanceDate,
                "end_date" => $action === "checkout" ? $attendanceDate : null,
                "observation" => $this->trans("biometric_record_observation", ["device_name" => $device->name]),
                "user_id" => 0, // System user
                "type" => "biometric",
                "action" => $action
            ]);

            if($result["bool"]) {

                return response()->json([
                    "bool" => true,
                    "msg" => $result["msg"],
                    "action" => $result["action"] ?? $action,
                    "customer" => [
                        "id" => $customer->id,
                        "name" => $customer->name
                    ]
                ], 200);

            }

            return response()->json(["bool" => false, "msg" => $result["msg"]], 422);

        }catch(\Exception $e) {

            Log::error("Error processing biometric event: " . $e->getMessage(), ["request" => $request->all(), "trace" => $e->getTraceAsString()]);

            return response()->json(["bool" => false, "msg" => $this->trans("event_processing_error")], 500);

        }

    }

}

