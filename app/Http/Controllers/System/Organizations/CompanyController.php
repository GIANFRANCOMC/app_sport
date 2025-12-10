<?php

namespace App\Http\Controllers\System\Organizations;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Companies\{StoreCompanyRequest, UpdateCompanyRequest};
use App\Models\System\General\{IdentityDocumentType};
use App\Models\System\Organizations\{Company, CompanySocialMedia};

class CompanyController extends Controller {

    public function initParams(Request $request) {

        $userAuth = Auth::user();

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->companies = new stdClass();
            $config->companies->statuses = Company::getStatuses();

            $company = $userAuth->company;
            $socialsMedia = $company->socialsMedia;

            $company->facebook  = optional($socialsMedia->where("type", "facebook")->first())->link;
            $company->instagram = optional($socialsMedia->where("type", "instagram")->first())->link;
            $company->whatsapp  = optional($socialsMedia->where("type", "whatsapp")->first())->link;

            $config->company = new stdClass();
            $config->company->records = [$company];

            $config->identityDocumentTypes = new stdClass();
            $config->identityDocumentTypes->records = IdentityDocumentType::getAll("company");

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        //

    }

    public function index() {

        return view("System/general/companies/main");

    }

    public function create() {

        //

    }

    public function store(StoreCompanyRequest $request) {

        //

    }

    public function show(Company $company) {

        //

    }

    public function edit(Company $company) {

        //

    }

    public function update(UpdateCompanyRequest $request, $id) {

        $userAuth = Auth::user();

        $company = Company::where("id", $userAuth->company_id)
                          ->whereIn("status", ["active"])
                          ->first();

        if(Utilities::isDefined($company)) {

            // Assets
            $logotypeRoute = null;
            $combinationmarkRoute = null;
            $logomarkRoute = null;
            $loginImageRoute = null;

            if($request->hasFile("logotype") && $request->file("logotype")) {

                $logotype = $request->file("logotype");

                $logotypeName  = "logotype.".$logotype->getClientOriginalExtension();
                $logotypeRoute = $logotype->storeAs($company->internal_code, $logotypeName, "public");

                $company->logotype = $logotypeRoute;

            }

            if($request->hasFile("combinationmark") && $request->file("combinationmark")) {

                $combinationmark = $request->file("combinationmark");

                $combinationmarkName  = "combinationmark.".$combinationmark->getClientOriginalExtension();
                $combinationmarkRoute = $combinationmark->storeAs($company->internal_code, $combinationmarkName, "public");

                $company->combinationmark = $combinationmarkRoute;

            }

            if($request->hasFile("logomark") && $request->file("logomark")) {

                $logomark = $request->file("logomark");

                $logomarkName  = "logomark.".$logomark->getClientOriginalExtension();
                $logomarkRoute = $logomark->storeAs($company->internal_code, $logomarkName, "public");

                $company->logomark = $logomarkRoute;

            }

            if($request->hasFile("login_image") && $request->file("login_image")) {

                $loginImage = $request->file("login_image");

                $loginImageName  = "login_image.".$loginImage->getClientOriginalExtension();
                $loginImageRoute = $loginImage->storeAs($company->internal_code, $loginImageName, "public");

                $company->login_image = $loginImageRoute;

            }

            DB::transaction(function() use($request, $userAuth, &$company) {

                $company->identity_document_type_id = $request->identity_document_type_id;
                $company->document_number           = $request->document_number;
                $company->legal_name                = $request->legal_name;
                $company->commercial_name           = $request->commercial_name;
                $company->tagline                   = $request->tagline;
                $company->description               = $request->description;
                $company->address                   = $request->address;
                $company->telephone                 = $request->telephone;
                $company->email                     = $request->email;
                // $company->token_api_misc            = $request->token_api_misc;
                // $company->status                    = $request->status;
                $company->updated_at                = now();
                $company->updated_by                = $userAuth->id ?? null;
                $company->save();

                $facebook = CompanySocialMedia::where("company_id", $company->id)
                                              ->where("type", "facebook")
                                              ->first();

                if(Utilities::isDefined($facebook)) {

                    $facebook->link       = $request->facebook ?? "";
                    $facebook->status     = "active";
                    $facebook->updated_at = now();
                    $facebook->updated_by = $userAuth->id ?? null;
                    $facebook->save();

                }else {

                    $companySocialMedia = new CompanySocialMedia();
                    $companySocialMedia->company_id = $company->id;
                    $companySocialMedia->type       = "facebook";
                    $companySocialMedia->link       = $request->facebook ?? "";
                    $companySocialMedia->status     = "active";
                    $companySocialMedia->created_at = now();
                    $companySocialMedia->created_by = $userAuth->id ?? null;
                    $companySocialMedia->save();

                }

                $instagram = CompanySocialMedia::where("company_id", $company->id)
                                               ->where("type", "instagram")
                                               ->first();

                if(Utilities::isDefined($instagram)) {

                    $instagram->link       = $request->instagram ?? "";
                    $instagram->status     = "active";
                    $instagram->updated_at = now();
                    $instagram->updated_by = $userAuth->id ?? null;
                    $instagram->save();

                }else {

                    $companySocialMedia = new CompanySocialMedia();
                    $companySocialMedia->company_id = $company->id;
                    $companySocialMedia->type       = "instagram";
                    $companySocialMedia->link       = $request->instagram ?? "";
                    $companySocialMedia->status     = "active";
                    $companySocialMedia->created_at = now();
                    $companySocialMedia->created_by = $userAuth->id ?? null;
                    $companySocialMedia->save();

                }

                $whatsapp = CompanySocialMedia::where("company_id", $company->id)
                                              ->where("type", "whatsapp")
                                              ->first();

                if(Utilities::isDefined($whatsapp)) {

                    $whatsapp->link       = $request->whatsapp ?? "";
                    $whatsapp->type       = "instagram";
                    $whatsapp->updated_at = now();
                    $whatsapp->updated_by = $userAuth->id ?? null;
                    $whatsapp->save();

                }else {

                    $companySocialMedia = new CompanySocialMedia();
                    $companySocialMedia->company_id = $company->id;
                    $companySocialMedia->type       = "whatsapp";
                    $companySocialMedia->link       = $request->whatsapp ?? "";
                    $companySocialMedia->status     = "active";
                    $companySocialMedia->created_at = now();
                    $companySocialMedia->created_by = $userAuth->id ?? null;
                    $companySocialMedia->save();

                }

            });

        }

        $bool = Utilities::isDefined($company);
        $msg  = $bool ? "Empresa editada correctamente." : "No se ha podido editar la empresa.";

        return response()->json(["bool" => $bool, "msg" => $msg, "company" => $company], 200);

    }

    public function destroy(Company $company) {

        //

    }

}
