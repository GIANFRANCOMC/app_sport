<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Branches\{StoreBranchRequest, UpdateBranchRequest};
use App\Models\System\General\{DocumentType};
use App\Models\System\Organizations\{Branch, Serie};
use App\Models\System\Warehouses\{Warehouse};

class BranchController extends Controller {

    public function initParams(Request $request) {

        $userAuth = Auth::user();

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->branches = new stdClass();
            $config->branches->statuses = Branch::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = Branch::when(Utilities::isDefined($request->filter_by) && Utilities::isDefined($request->word), function($query) use($request) {

                            $filter = Utilities::getWordSearch($request->word);

                            if(in_array($request->filter_by, ["all"])) {

                                $query->where(function($query) use($filter) {

                                    $query->where("internal_code", "like", $filter)
                                          ->orWhere("name", "like", $filter)
                                          ->orWhere("address", "like", $filter)
                                          ->orWhere("reference", "like", $filter)
                                          ->orWhere("telephone", "like", $filter)
                                          ->orWhere("email", "like", $filter);

                                });

                            }else if(in_array($request->filter_by, ["internal_code", "name", "address", "reference", "telephone", "email"])) {

                                $query->where($request->filter_by, "like", $filter);

                            }

                      })
                      ->where("company_id", $userAuth->company_id)
                      ->orderBy("name", "ASC")
                      ->with(["series.documentType", "warehouses"])
                      ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/branches/main");

    }

    public function create() {

        //

    }

    public function store(StoreBranchRequest $request) {

        $userAuth = Auth::user();

        $branch = null;

        DB::transaction(function() use($request, $userAuth, &$branch) {

            $newSequential = Serie::getNewSequential($userAuth->company_id);

            $branch = new Branch();
            $branch->company_id    = $userAuth->company_id;
            $branch->internal_code = $request->internal_code;
            $branch->name          = $request->name;
            $branch->address       = $request->address;
            $branch->reference     = $request->reference;
            $branch->telephone     = $request->telephone;
            $branch->email         = $request->email;
            $branch->capacity      = $request->capacity;
            $branch->map_url       = $request->map_url;
            $branch->status        = $request->status;
            $branch->created_at    = now();
            $branch->created_by    = $userAuth->id ?? null;
            $branch->save();

            $documentTypes = DocumentType::whereIn("status", ["active"])
                                         ->get();

            foreach($documentTypes as $documentType) {

                $serie = new Serie();
                $serie->branch_id        = $branch->id;
                $serie->document_type_id = $documentType->id;
                $serie->code             = $documentType->code;
                $serie->number           = $newSequential;
                $serie->init             = 1;
                $serie->status           = "active";
                $serie->created_at       = now();
                $serie->created_by       = $userAuth->id ?? null;
                $serie->save();

            }

            $seq = 1;

            $warehouse = new Warehouse();
            $warehouse->branch_id  = $branch->id;
            $warehouse->name       = "$branch->name - Almacén $seq";
            $warehouse->status     = "active";
            $warehouse->created_at = now();
            $warehouse->created_by = $userAuth->id ?? null;
            $warehouse->save();

        });

        $bool = Utilities::isDefined($branch);
        $msg  = $bool ? "Sucursal creada correctamente." : "No se ha podido crear la sucursal.";

        return response()->json(["bool" => $bool, "msg" => $msg, "branch" => $branch], 200);

    }

    public function show(Branch $record) {

        //

    }

    public function edit(Branch $record) {

        //

    }

    public function update(UpdateBranchRequest $request, $id) {

        $userAuth = Auth::user();

        $branch = Branch::where("id", $id)
                        ->where("company_id", $userAuth->company_id)
                        ->with(["warehousesAll"])
                        ->first();

        if(Utilities::isDefined($branch)) {

            DB::transaction(function() use($request, $userAuth, &$branch) {

                $branch->internal_code = $request->internal_code;
                $branch->name          = $request->name;
                $branch->address       = $request->address;
                $branch->reference     = $request->reference;
                $branch->telephone     = $request->telephone;
                $branch->email         = $request->email;
                $branch->capacity      = $request->capacity;
                $branch->map_url       = $request->map_url;
                $branch->status        = $request->status;
                $branch->updated_at    = now();
                $branch->updated_by    = $userAuth->id ?? null;
                $branch->save();

                $seq = 1;

                foreach($branch->warehousesAll as $warehouse) {

                    $warehouse->name       = "$branch->name - Almacén $seq";
                    $warehouse->updated_at = now();
                    $warehouse->updated_by = $userAuth->id ?? null;
                    $warehouse->save();

                    $seq++;

                }

            });

        }

        $bool = Utilities::isDefined($branch);
        $msg  = $bool ? "Sucursal editada correctamente." : "No se ha podido editar la sucursal.";

        return response()->json(["bool" => $bool, "msg" => $msg, "branch" => $branch], 200);

    }

    public function destroy(Branch $record) {

        //

    }

}
