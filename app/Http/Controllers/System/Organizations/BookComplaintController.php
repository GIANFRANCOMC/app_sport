<?php

namespace App\Http\Controllers\System\Organizations;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\BookComplaints\{StoreBookComplaintRequest, UpdateBookComplaintRequest};
use App\Models\System\General\{IdentityDocumentType};
use App\Models\System\Organizations\{BookComplaint};

class BookComplaintController extends Controller {

    public function initParams(Request $request) {

        $userAuth = Auth::user();

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->identityDocumentTypes = new stdClass();
            $config->identityDocumentTypes->records = IdentityDocumentType::getAll("book_complaint", $userAuth->company_id);

            $config->bookComplaints = new stdClass();
            $config->bookComplaints->types    = BookComplaint::getTypes();
            $config->bookComplaints->statuses = BookComplaint::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = BookComplaint::when(Utilities::isDefined($request->filter_by), function($query) use($request) {

                                $filter = Utilities::getWordSearch($request->word);

                                if(in_array($request->filter_by, ["all"])) {

                                    $query->where(function($query) use($request, $filter) {

                                        $query->where("document_number", "like", $filter)
                                              ->orWhere("name", "like", $filter)
                                              ->orWhere("email", "like", $filter)
                                              ->orWhere("phone_number", "like", $filter);

                                    });

                                }else if(in_array($request->filter_by, ["document_number", "name", "email", "phone_number"])) {

                                    $query->where(function($query) use($request, $filter) {

                                        $query->where($request->filter_by, "like", $filter);

                                    });

                                }

                             })
                             ->where("company_id", $userAuth->company_id)
                             ->orderBy("id", "DESC")
                             ->with(["branch", "identityDocumentType"])
                             ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/Organizations/book_complaints/main");

    }

    public function create() {

        //

    }

    public function store(StoreBookComplaintRequest $request) {

        //

    }

    public function show(BookComplaint $record) {

        //

    }

    public function edit(BookComplaint $record) {

        //

    }

    public function update(UpdateBookComplaintRequest $request, $id) {

        $userAuth = Auth::user();

        $bookComplaint = BookComplaint::where("id", $id)
                                      ->where("company_id", $userAuth->company_id)
                                      ->first();

        if(Utilities::isDefined($bookComplaint)) {

            DB::transaction(function() use($request, $userAuth, &$bookComplaint) {

                $bookComplaint->admin_response = $request->admin_response;
                $bookComplaint->status         = $request->status;
                $bookComplaint->updated_at     = now();
                $bookComplaint->updated_by     = $userAuth->id ?? null;
                $bookComplaint->save();

            });

        }

        $bool = Utilities::isDefined($bookComplaint);
        $msg  = $bool ? "Respuesta registrada correctamente." : "No se ha podido registrar la respuesta.";

        return response()->json(["bool" => $bool, "msg" => $msg, "bookComplaint" => $bookComplaint], 200);

    }

    public function destroy(BookComplaint $record) {

        //

    }

}
