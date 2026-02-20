<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\System\Organizations\Branches\{StoreBranchRequest, UpdateBranchRequest};
use App\Services\System\Organizations\Branches\{BranchConfigService, BranchService};
use App\Models\System\Organizations\{Branch};

class BranchController extends BaseController {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.branch";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);

        return BranchConfigService::getInitParams($this->getCompanyId(), $page);

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

        return BranchService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the module index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Organizations/branches/main");

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
     * @param StoreBranchRequest $request
     * @return JsonResponse
     */
    public function store(StoreBranchRequest $request): JsonResponse {

        try {

            $data   = $this->prepareBranchData($request);
            $branch = BranchService::create($data, $this->getUserId());

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("create_failed");

            }

            BranchConfigService::clearAllCache($this->getCompanyId());

            return $this->createdResponse($branch, "created", "branch");

        }catch(\Exception $e) {

            return $this->handleException($e, "create");

        }

    }

    /**
     * Display the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Branch $record
     * @return JsonResponse
     */
    public function show(Branch $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified record
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Branch $record
     * @return void
     */
    public function edit(Branch $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified record
     *
     * @param UpdateBranchRequest $request
     * @param int $id Branch ID
     * @return JsonResponse
     */
    public function update(UpdateBranchRequest $request, int $id): JsonResponse {

        try {

            $branch = BranchService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($branch)) {

                return $this->notFoundResponse();

            }

            $data   = $this->prepareBranchData($request);
            $branch = BranchService::update($branch, $data, $this->getUserId());

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("update_failed");

            }

            BranchConfigService::clearAllCache($this->getCompanyId());

            return $this->updatedResponse($branch, "updated", "branch");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified record
     * (Not used, but kept for REST compliance)
     *
     * @param Branch $record
     * @return JsonResponse
     */
    public function destroy(Branch $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare record data from request
     *
     * @param StoreBranchRequest|UpdateBranchRequest $request
     * @return array
     */
    private function prepareBranchData($request): array {

        return [
            "company_id"    => $this->getCompanyId(),
            "internal_code" => $request->input("internal_code"),
            "name"          => $request->input("name"),
            "address"       => $request->input("address"),
            "reference"     => $request->input("reference"),
            "telephone"     => $request->input("telephone"),
            "email"         => $request->input("email"),
            "capacity"      => $request->input("capacity"),
            "map_url"       => $request->input("map_url"),
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
