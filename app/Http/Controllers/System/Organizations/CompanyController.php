<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Organizations;

use Exception;
use App\Http\Controllers\{Controller};
use App\Helpers\System\{Utilities};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth};

use App\Http\Controllers\System\Concerns\{HandlesApiResponses};
use App\Http\Requests\System\Organizations\Companies\{StoreCompanyRequest, UpdateCompanyRequest};
use App\Services\System\Organizations\Companies\{CompanyConfigService, CompanyService};
use App\Models\System\Organizations\{Company};

class CompanyController extends Controller {

    use HandlesApiResponses;

    /**
     * Translation namespace for company module
     */
    private const TRANSLATION_NAMESPACE = "System.Organizations.company";

    /**
     * Get initialization parameters for the module
     *
     * @param Request $request
     * @return \stdClass
     */
    public function initParams(Request $request) {

        $userAuth = Auth::user();
        $page     = $request->input("page", "");

        return CompanyConfigService::getInitParams($userAuth->company_id, $page);

    }

    /**
     * Get paginated list of companies with filters
     * (Not used for companies, but kept for REST compliance)
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request): void {

        // Not implemented - companies are managed individually

    }

    /**
     * Display the companies index page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index() {

        return view("System/general/Organizations/companies/main");

    }

    /**
     * Show the form for creating a new company
     * (Not used in SPA, but kept for REST compliance)
     *
     * @return void
     */
    public function create(): void {

        // Form is handled by frontend SPA

    }

    /**
     * Store a newly created company
     * (Not used, but kept for REST compliance)
     *
     * @param StoreCompanyRequest $request
     * @return JsonResponse
     */
    public function store(StoreCompanyRequest $request): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Display the specified company
     * (Not used, but kept for REST compliance)
     *
     * @param Company $company
     * @return JsonResponse
     */
    public function show(Company $company): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Show the form for editing the specified company
     * (Not used in SPA, but kept for REST compliance)
     *
     * @param Company $company
     * @return void
     */
    public function edit(Company $company): void {

        // Form is handled by frontend SPA

    }

    /**
     * Update the specified company
     *
     * @param UpdateCompanyRequest $request
     * @param int $id Company ID
     * @return JsonResponse
     */
    public function update(UpdateCompanyRequest $request, int $id): JsonResponse {

        try {

            $userAuth = Auth::user();
            $company  = CompanyService::findByAuthUser();

            if(!Utilities::isDefined($company) || $company->id != $id) {

                return $this->notFoundResponse();

            }

            $data  = $this->prepareCompanyData($request);
            $files = $this->prepareCompanyFiles($request);
            $company = CompanyService::update($company, $data, $files, $userAuth->id);

            if(!Utilities::isDefined($company)) {

                return $this->errorResponse("update_failed");

            }

            CompanyConfigService::clearAllCache($userAuth->company_id);

            return $this->updatedResponse($company, "updated", "company");

        }catch(Exception $e) {

            return response()->json(["bool" => false, "msg" => $e->getMessage()], 200);

        }

    }

    /**
     * Remove the specified company
     * (Not used, but kept for REST compliance)
     *
     * @param Company $company
     * @return JsonResponse
     */
    public function destroy(Company $company): JsonResponse {

        return $this->errorResponse("not_implemented", [], 501);

    }

    /**
     * Prepare company data from request
     *
     * @param UpdateCompanyRequest $request
     * @return array
     */
    private function prepareCompanyData(UpdateCompanyRequest $request): array {

        return [
            "identity_document_type_id" => $request->identity_document_type_id,
            "document_number"           => $request->document_number,
            "legal_name"                => $request->legal_name,
            "commercial_name"           => $request->commercial_name,
            "tagline"                   => $request->tagline,
            "description"               => $request->description,
            "address"                   => $request->address,
            "telephone"                 => $request->telephone,
            "email"                     => $request->email,
            "facebook"                  => $request->facebook,
            "instagram"                 => $request->instagram,
            "whatsapp"                  => $request->whatsapp
        ];

    }

    /**
     * Prepare company files from request
     *
     * @param UpdateCompanyRequest $request
     * @return array
     */
    private function prepareCompanyFiles(UpdateCompanyRequest $request): array {

        $files = [];

        $imageFields = ["logotype", "combinationmark", "logomark", "login_image"];

        foreach($imageFields as $field) {

            if($request->hasFile($field) && $request->file($field)) {

                $files[$field] = $request->file($field);

            }

        }

        return $files;

    }

    /**
     * Get translation namespace for company module
     *
     * @return string
     */
    protected function getTranslationNamespace(): string {

        return self::TRANSLATION_NAMESPACE;

    }

}
