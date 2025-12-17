<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Customers;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Customers\{StoreCustomerRequest, UpdateCustomerRequest};
use App\Services\System\Customers\{CustomerConfigService, CustomerService};
use App\Models\System\Customers\{Customer, Subscription};

class CustomerController extends Controller {

    use HandlesApiResponses;

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

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return CustomerConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of customers with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return CustomerService::getPaginatedList($userAuth->company_id, $filters, $perPage);

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

            $userAuth = Auth::user();
            $data     = $this->prepareCustomerData($request, $userAuth);
            $customer = CustomerService::create($data, $userAuth->id);

            if(!Utilities::isDefined($customer)) {

                return $this->errorResponse("create_failed");

            }

            CustomerConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($customer, "created", "customer");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

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

            $userAuth = Auth::user();
            $customer = CustomerService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($customer)) {

                return $this->notFoundResponse();

            }

            $data     = $this->prepareCustomerData($request, $userAuth);
            $customer = CustomerService::update($customer, $data, $userAuth->id);

            if(!Utilities::isDefined($customer)) {

                return $this->errorResponse("update_failed");

            }

            CustomerConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($customer, "updated", "customer");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

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

        $userAuth = Auth::user();

        $customer = CustomerService::findByIdAndCompany($id, $userAuth->company_id);

        $subscriptions = [];

        if(Utilities::isDefined($customer)) {

            $subscriptions = Subscription::where("company_id", $userAuth->company_id)
                                         ->where("customer_id", $customer->id)
                                         ->whereIn("status", ["active"])
                                         ->get();

        }

        $bool = Utilities::isDefined($customer);
        $msg  = $bool ? "Membresías encontradas." : "No se ha podido identificar el cliente.";

        return response()->json(["bool" => $bool, "msg" => $msg, "subscriptions" => $subscriptions], 200);

    }

    /**
     * Prepare customer data from request
     *
     * @param StoreCustomerRequest|UpdateCustomerRequest $request
     * @param object|null $userAuth
     * @return array
     */
    private function prepareCustomerData($request, ?object $userAuth = null): array {

        $data = [
            "identity_document_type_id" => $request->identity_document_type_id,
            "document_number"           => $request->document_number,
            "name"                      => $request->name,
            "email"                     => $request->email,
            "phone_number"              => $request->phone_number,
            "gender"                    => $request->gender ?? "other",
            "birthdate"                 => $request->birthdate,
            "status"                    => $request->status
        ];

        if($userAuth) {

            $data["company_id"] = $userAuth->company_id;

        }

        return $data;

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
