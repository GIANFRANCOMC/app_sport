<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Branches\{StoreBranchRequest, UpdateBranchRequest};
use App\Services\System\Organizations\{BranchConfigService, BranchService};
use App\Models\System\Organizations\{Branch};

class BranchController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for branch module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.branch";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $request->input("page", "");

        return BranchConfigService::getInitParams($page);

    }

    /**
     * Get paginated list of branches with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $userAuth = Auth::user();
        $filters  = ["filter_by" => $request->input("filter_by"), "word" => $request->input("word")];
        $perPage  = intval($request->input("per_page") ?? Utilities::$per_page_default);

        return BranchService::getPaginatedList($userAuth->company_id, $filters, $perPage);

    }

    /**
     * Display the branches index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/branches/main");

    }

    /**
     * Show the form for creating a new branch
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created branch
     *
     * @param StoreBranchRequest $request
     * @return JsonResponse
     */
    public function store(StoreBranchRequest $request): JsonResponse {

        try {

            $userAuth = Auth::user();
            $data     = $this->prepareBranchData($request, $userAuth);
            $branch   = BranchService::create($data, $userAuth->id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("create_failed");

            }

            BranchConfigService::clearAllCache();

            return $this->createdResponse($branch, "created", "branch");

        }catch(Exception $e) {

            return $this->errorResponse("exception_create", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Display the specified branch
     * (Not used, but kept for REST compliance)
     *
     * @param Branch $record
     * @return JsonResponse
     */
    public function show(Branch $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified branch
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Branch $record
     * @return void
     */
    public function edit(Branch $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified branch
     *
     * @param UpdateBranchRequest $request
     * @param int $id Branch ID
     * @return JsonResponse
     */
    public function update(UpdateBranchRequest $request, int $id): JsonResponse {

        try {

            $userAuth = Auth::user();
            $branch   = BranchService::findByIdAndCompany($id, $userAuth->company_id);

            if(!Utilities::isDefined($branch)) {

                return $this->notFoundResponse();

            }

            $data   = $this->prepareBranchData($request, $userAuth);
            $branch = BranchService::update($branch, $data, $userAuth->id);

            if(!Utilities::isDefined($branch)) {

                return $this->errorResponse("update_failed");

            }

            BranchConfigService::clearAllCache();

            return $this->updatedResponse($branch, "updated", "branch");

        }catch(Exception $e) {

            return $this->errorResponse("exception_update", ["message" => $e->getMessage()]);

        }

    }

    /**
     * Remove the specified branch
     * (Not used, but kept for REST compliance)
     *
     * @param Branch $record
     * @return JsonResponse
     */
    public function destroy(Branch $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare branch data from request
     *
     * @param StoreBranchRequest|UpdateBranchRequest $request
     * @param object|null $userAuth
     * @return array
     */
    private function prepareBranchData($request, ?object $userAuth = null): array {

        $data = [
            "internal_code" => $request->internal_code,
            "name"          => $request->name,
            "address"       => $request->address,
            "reference"     => $request->reference,
            "telephone"     => $request->telephone,
            "email"         => $request->email,
            "capacity"      => $request->capacity,
            "map_url"       => $request->map_url,
            "status"        => $request->status
        ];

        if($userAuth) {

            $data["company_id"] = $userAuth->company_id;

        }

        return $data;

    }

    /**
     * Get translation namespace for branch module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
