<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use Exception;
use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Customers\Customers\{StoreCustomerRequest, UpdateCustomerRequest};
use App\Services\System\Customers\{CustomerConfigService, CustomerService};
use App\Models\System\Customers\{Customer, Subscription};

class CustomerController extends BaseController {

    /**
     * Translation namespace for customer module
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
     * Get paginated list of customers with filters
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
     * Display the customers index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Customers/customers/main");

    }

    /**
     * Show the form for creating a new customer
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created customer
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
     * Display the specified customer
     * (Not used, but kept for REST compliance)
     *
     * @param Customer $record
     * @return JsonResponse
     */
    public function show(Customer $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified customer
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Customer $record
     * @return void
     */
    public function edit(Customer $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified customer
     *
     * @param UpdateCustomerRequest $request
     * @param int $id Customer ID
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse {

        try {

            $customer = CustomerService::findByIdAndCompany($id, $this->getCompanyId());

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
     * Remove the specified customer
     * (Not used, but kept for REST compliance)
     *
     * @param Customer $record
     * @return JsonResponse
     */
    public function destroy(Customer $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Get subscriptions for a customer
     *
     * @param int $id Customer ID
     * @return JsonResponse
     */
    public function getSubscriptions(int $id): JsonResponse {

        try {

            $customer = CustomerService::findByIdAndCompany($id, $this->getCompanyId());

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
     * Prepare customer data from request
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
     * Get translation namespace for customer module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
