<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Biometric;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Http\Requests\System\Biometric\{StoreBiometricDeviceRequest, UpdateBiometricDeviceRequest};
use App\Services\System\Biometric\{BiometricDeviceConfigService, BiometricDeviceService};
use App\Services\System\Customers\Tracking\{TrackingAttendanceBusinessService};
use App\Models\System\Biometric\{BiometricDevice};

class BiometricDeviceController extends BaseController {

    /**
     * Translation namespace for biometric device module
     */
    private const TRANSLATION_NAMESPACE = "System.Biometric.biometric_device";

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
     * Get paginated list of biometric devices with filters
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
     * Display the biometric devices index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Biometric/biometric_devices/main");

    }

    /**
     * Show the form for creating a new biometric device
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created biometric device
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
     * Display the specified biometric device
     * (Not used, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return JsonResponse
     */
    public function show(BiometricDevice $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified biometric device
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return void
     */
    public function edit(BiometricDevice $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified biometric device
     *
     * @param UpdateBiometricDeviceRequest $request
     * @param int $id Biometric Device ID
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
     * Remove the specified biometric device
     * (Not used, but kept for REST compliance)
     *
     * @param BiometricDevice $record
     * @return JsonResponse
     */
    public function destroy(BiometricDevice $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare biometric device data from request
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
     * Get translation namespace for biometric device module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    /**
     * Receive event from ZKTeco device
     * This endpoint is called by the biometric device when a fingerprint is scanned
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

                return response()->json(["bool" => false, "msg" => "No se pudo identificar la empresa."], 400);

            }

            // Validate required parameters
            $deviceUserId = $request->input("user_id"); // User ID from the device

            if(!Utilities::isDefined($deviceUserId)) {

                return response()->json(["bool" => false, "msg" => "El parámetro 'user_id' es requerido."], 400);

            }

            // Get device identifier (IP address or device_id)
            $deviceIp  = $request->ip();
            $deviceId  = $request->input("device_id");
            $timestamp = $request->input("timestamp", now());
            $action    = $request->input("action", "checkin"); // "checkin" or "checkout"

            // Validate action
            if(!in_array($action, ["checkin", "checkout"])) {

                return response()->json(["bool" => false, "msg" => "El parámetro 'action' debe ser 'checkin' o 'checkout'."], 400);

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

                return response()->json(["bool" => false, "msg" => "Dispositivo biométrico no encontrado o no autorizado. Verifique la configuración del dispositivo."], 404);

            }

            // Get customer by device user ID
            $customer = BiometricDeviceService::findCustomerByDeviceUserId($device->id, (int) $deviceUserId, $device->company_id);

            if(!Utilities::isDefined($customer)) {

                Log::warning("Biometric customer not found", ["device_id" => $device->id, "device_user_id" => $deviceUserId, "company_id" => $companyId]);

                return response()->json(["bool" => false, "msg" => "Usuario no encontrado en el sistema. Verifique que la huella esté registrada correctamente."], 404);

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
                "observation" => "Registro biométrico - " . $device->name,
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

            return response()->json(["bool" => false, "msg" => "Error al procesar el evento biométrico."], 500);

        }

    }

    /**
     * Get active biometric devices for the company
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDevices(Request $request): JsonResponse {

        try {

            $branchId = $request->input("branch_id");

            $devices = BiometricDeviceService::getActiveDevices($this->getCompanyId(), Utilities::isDefined($branchId) ? (int) $branchId : null);

            return response()->json([
                "bool" => true,
                "data" => [
                    "devices" => $devices->map(function($device) {
                        return [
                            "id" => $device->id,
                            "name" => $device->name,
                            "brand" => $device->brand,
                            "model" => $device->model,
                            "ip_address" => $device->ip_address,
                            "port" => $device->port,
                            "branch" => [
                                "id" => $device->branch->id,
                                "name" => $device->branch->name
                            ]
                        ];
                    })
                ]
            ]);

        }catch(\Exception $e) {

            return $this->handleException($e, "list");

        }

    }

}

