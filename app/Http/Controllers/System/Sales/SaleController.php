<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Sales;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Sales\{CancelSaleRequest, StoreSaleRequest, UpdateSaleRequest};
use App\Services\System\Sales\{SaleConfigService, SaleService};
use App\Models\System\Sales\{SaleHeader};

class SaleController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for sale module
     */
    private const TRANSLATION_NAMESPACE = "System.Sales.sale";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return SaleConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of sales with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = [
            "serie_id"   => $request->input("serie_id"),
            "sequential" => $request->input("sequential"),
            "issue_date" => $request->input("issue_date"),
            "holder_id"  => $request->input("holder_id"),
            "status"     => $request->input("status")
        ];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return SaleService::getPaginatedList($userAuth->company_id, $filters, $perPage);

    }

    public function index() {

        return view("System/general/Sales/sales/list");

    }

    public function create() {

        return view("System/general/Sales/sales/main");

    }

    /**
     * Store a newly created sale
     *
     * @param StoreSaleRequest $request
     * @return JsonResponse
     */
    public function store(StoreSaleRequest $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $data     = $this->prepareSaleData($request);
            $sale     = SaleService::create($data, $userAuth->id);

            if(!Utilities::isDefined($sale)) {

                return $this->errorResponse("create_failed");

            }

            SaleConfigService::clearAllCache($userAuth->company_id);

            return $this->createdResponse($sale, "created", "sale");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Display the specified sale
     * (Not used, but kept for REST compliance)
     *
     * @param SaleHeader $record
     * @return JsonResponse
     */
    public function show(SaleHeader $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified sale
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param SaleHeader $record
     * @return void
     */
    public function edit(SaleHeader $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified sale
     * (Not implemented, but kept for REST compliance)
     *
     * @param UpdateSaleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSaleRequest $request, int $id): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Cancel the specified sale
     *
     * @param CancelSaleRequest $request
     * @param int $id Sale ID
     * @return JsonResponse
     */
    public function cancel(CancelSaleRequest $request, int $id): JsonResponse {

        try {

            $userAuth = Auth::user();
            $sale     = SaleService::findById($id);

            if(!Utilities::isDefined($sale)) {

                return $this->notFoundResponse();

            }

            // Verify company ownership
            if($serie = $sale->serie) {

                $branch = $serie->branch;

                if(!Utilities::isDefined($branch) || $branch->company_id != $userAuth->company_id) {

                    return $this->errorResponse("unauthorized", [], 403);

                }

            }

            $sale = SaleService::cancel($sale, $userAuth->id);

            if(!Utilities::isDefined($sale)) {

                return $this->errorResponse("cancel_failed");

            }

            SaleConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($sale, "canceled", "sale");

        }catch(Exception $e) {

            return $this->errorResponse("exception_cancel", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Prepare sale data from request
     *
     * @param StoreSaleRequest $request
     * @return array
     */
    private function prepareSaleData(StoreSaleRequest $request): array {

        return [
            "branch_id"   => $request->branch_id,
            "serie_id"    => $request->serie_id,
            "holder_id"   => $request->holder_id,
            "currency_id" => $request->currency_id,
            "issue_date"  => $request->issue_date,
            "observation" => $request->observation,
            "details"     => $request->details
        ];

    }

    /**
     * Get translation namespace for sale module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

    /**
     * Remove the specified sale
     * (Not used, but kept for REST compliance)
     *
     * @param SaleHeader $record
     * @return JsonResponse
     */
    public function destroy(SaleHeader $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

}
