<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use App\Http\Controllers\System\Base\BaseController;
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};

use App\Http\Requests\System\Organizations\BookComplaints\{UpdateBookComplaintRequest};
use App\Services\System\Organizations\BookComplaints\{BookComplaintConfigService, BookComplaintService};
use App\Models\System\Organizations\{BookComplaint};

class BookComplaintController extends BaseController {

    /**
     * Translation namespace for book complaint module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.book_complaint";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $page = $this->getPage($request);
        return BookComplaintConfigService::getInitParams($this->getCompanyId(), $page);

    }

    /**
     * Get paginated list of book complaints with filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request) {

        $filters = $this->getFilters($request);
        $perPage = $this->getPerPage($request, Utilities::$per_page_default);

        return BookComplaintService::getPaginatedList($this->getCompanyId(), $filters, $perPage);

    }

    /**
     * Display the book complaints index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Organizations/book_complaints/main");

    }

    /**
     * Show the form for creating a new book complaint
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created book complaint
     * (Not used in System, book complaints are created from Guest side)
     *
     * @return JsonResponse
     */
    public function store(): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Display the specified book complaint
     * (Not used, but kept for REST compliance)
     *
     * @param BookComplaint $record
     * @return JsonResponse
     */
    public function show(BookComplaint $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified book complaint
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param BookComplaint $record
     * @return void
     */
    public function edit(BookComplaint $record): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified book complaint
     *
     * @param UpdateBookComplaintRequest $request
     * @param int $id Book complaint ID
     * @return JsonResponse
     */
    public function update(UpdateBookComplaintRequest $request, int $id): JsonResponse {

        try {

            $bookComplaint = BookComplaintService::findByIdAndCompany($id, $this->getCompanyId(), null);

            if(!Utilities::isDefined($bookComplaint)) {

                return $this->notFoundResponse();

            }

            $data         = $this->prepareBookComplaintData($request);
            $bookComplaint = BookComplaintService::update($bookComplaint, $data, $this->getUserId());

            if(!Utilities::isDefined($bookComplaint)) {

                return $this->errorResponse("update_failed");

            }

            return $this->updatedResponse($bookComplaint, "updated", "bookComplaint");

        }catch(\Exception $e) {

            return $this->handleException($e, "update");

        }

    }

    /**
     * Remove the specified book complaint
     * (Not used, but kept for REST compliance)
     *
     * @param BookComplaint $record
     * @return JsonResponse
     */
    public function destroy(BookComplaint $record): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare book complaint data from request
     *
     * @param UpdateBookComplaintRequest $request
     * @return array
     */
    private function prepareBookComplaintData(UpdateBookComplaintRequest $request): array {

        return [
            "admin_response" => $request->admin_response,
            "status"         => $request->status
        ];

    }

    /**
     * Get translation namespace for book complaint module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
