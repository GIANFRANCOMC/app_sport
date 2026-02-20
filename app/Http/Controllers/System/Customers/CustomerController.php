<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Customers\Customers\{StoreCustomerRequest, UpdateCustomerRequest};
use App\Services\System\Customers\Customers\{CustomerConfigService, CustomerService};
use App\Services\System\Devices\BiometricDevices\{BiometricDeviceService};
use App\Models\System\Customers\{Customer, Subscription};

class CustomerController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Customers.customer";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return CustomerConfigService::getInitParams($this->getCompanyId(), $page);

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

        return CustomerService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Customers/customers/main");

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
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse {

        try {

            $data     = $this->prepareCustomerData($request);
            $customer = CustomerService::create($data, $this->getUserId());

            if(!Utilities::isDefined($customer)) {

                return $this->errorResponse("create_failed");

            }

            CustomerConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($customer, "created", "customer");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Customer $record
     * @return JsonResponse
     */
    public function show(Customer $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Customer $record
     * @return void
     */
    public function edit(Customer $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateCustomerRequest $request
     * @param int $id Customer ID
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse {

        try {

            $customer = CustomerService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($customer)) {

                return $this->notFoundResponse();

            }

            $data     = $this->prepareCustomerData($request);
            $customer = CustomerService::update($customer, $data, $this->getUserId());

            if(!Utilities::isDefined($customer)) {

                return $this->errorResponse("update_failed");

            }

            CustomerConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($customer, "updated", "customer");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Customer $record
     * @return JsonResponse
     */
    public function destroy(Customer $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare record data from request
     *
     * @param StoreCustomerRequest|UpdateCustomerRequest $request
     * @return array
     */
    private function prepareCustomerData($request): array {

        return [
            "company_id"                => $this->getCompanyId(),
            "identity_document_type_id" => $request->identity_document_type_id,
            "document_number"           => $request->document_number,
            "name"                      => $request->name,
            "email"                     => $request->email,
            "phone_number"              => $request->phone_number,
            "gender"                    => $request->gender ?? "other",
            "birthdate"                 => $request->birthdate,
            "status"                    => $request->status
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
     * Get subscriptions for a customer
     *
     * @param int $id Customer ID
     * @return JsonResponse
     */
    public function getSubscriptions(int $id): JsonResponse {

        try {

            $customer = CustomerService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($customer)) {

                return $this->errorResponse("not_found", [], 404);

            }

            $subscriptions = Subscription::where("company_id", $this->getCompanyId())
                                         ->where("customer_id", $customer->id)
                                         ->whereIn("status", ["active"])
                                         ->get();

            return $this->successResponse(["subscriptions" => $subscriptions], "retrieved");

        }catch(\Exception $e) {

            return $this->handleException($e, "retrieve");

        }

    }

    /**
     * Register biometric fingerprint for customer
     *
     * @param Request $request
     * @param int $id Customer ID
     * @return JsonResponse
     */
    public function registerBiometricFingerprint(Request $request, int $id): JsonResponse {

        try {

            $customer = CustomerService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($customer)) {

                return $this->notFoundResponse();

            }

            $biometricDeviceId = $request->input("biometric_device_id");
            $deviceUserId      = $request->input("device_user_id");
            $fingerIndex       = $request->input("finger_index", 0);

            if(!Utilities::isDefined($biometricDeviceId)) {

                return $this->errorResponse("validation_error", ["msg" => "Se requiere dispositivo biométrico."], 422);

            }

            // Check if device exists and belongs to company
            $device = BiometricDeviceService::findByIdAndCompany((int)$biometricDeviceId, $this->getCompanyId(), null);

            if(!Utilities::isDefined($device)) {

                return $this->errorResponse("not_found", ["msg" => "Dispositivo biométrico no encontrado."], 404);

            }

            // Auto-assign device_user_id if not provided
            if(!Utilities::isDefined($deviceUserId)) {

                $deviceUserId = BiometricDeviceService::getNextDeviceUserId((int)$biometricDeviceId);

            }else {

                // Check if device_user_id and finger_index combination already exists
                if(BiometricDeviceService::deviceUserIdExists((int)$biometricDeviceId, (int)$deviceUserId, (int)$fingerIndex)) {

                    return $this->errorResponse("validation_error", ["msg" => "Ya existe una huella registrada para este usuario y dedo en el dispositivo."], 422);

                }

            }

            // Register fingerprint
            $fingerprint = BiometricDeviceService::registerFingerprint(
                $customer->id,
                (int)$biometricDeviceId,
                (int)$deviceUserId,
                (int)$fingerIndex,
                $this->getUserId(),
                $this->getCompanyId()
            );

            return $this->createdResponse($fingerprint, "fingerprint_registered", "biometric_fingerprint");

        }catch(\Exception $e) {

            return $this->handleException($e, "register_fingerprint");

        }

    }

}
