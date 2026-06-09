<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Devices;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Devices\BiometricDevices\{StoreBiometricDeviceRequest, UpdateBiometricDeviceRequest};
use App\Services\System\Base\{InitParamsCacheInvalidationService};
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

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::BIOMETRIC_DEVICES,
                $this->getCompanyId()
            );

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

            $device = BiometricDeviceService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($device)) {

                return $this->notFoundResponse();

            }

            $data   = $this->prepareBiometricDeviceData($request);
            $device = BiometricDeviceService::update($device, $data, $this->getUserId());

            if(!Utilities::isDefined($device)) {

                return $this->errorResponse("update_failed");

            }

            InitParamsCacheInvalidationService::invalidate(
                InitParamsCacheInvalidationService::BIOMETRIC_DEVICES,
                $this->getCompanyId()
            );

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

}
