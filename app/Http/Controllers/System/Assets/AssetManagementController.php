<?php

namespace App\Http\Controllers\System\Assets;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Assets\AssetManagements\{AssignAssetToBranchRequest, UnassignAssetFromBranchRequest};
use App\Models\System\Assets\{Asset, AssetAssignment, BranchAsset};
use App\Models\System\Organizations\{User};
use App\Models\System\Organizations\{Branch};
use Illuminate\Pagination\LengthAwarePaginator;

class AssetManagementController extends Controller {

    public function initParams(Request $request) {

        $userAuth = Auth::user();

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->assets = new stdClass();
            $config->assets->records = Asset::getAll("asset_management", $userAuth->company_id);

            $config->branches = new stdClass();
            $config->branches->records = Branch::getAll("asset_management", $userAuth->company_id);

            $config->users = new stdClass();
            $config->users->records = User::getAll("asset_management", $userAuth->company_id);

            $config->branchAssets = new stdClass();
            $config->branchAssets->statuses = BranchAsset::getStatuses();

            $config->assetAssignments = new stdClass();
            $config->assetAssignments->statuses = AssetAssignment::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return new LengthAwarePaginator([], 0, 1, 1, ["path" => ""]);

        }


        $list = BranchAsset::where("branch_id", $branch->id)
                           ->with(["asset"])
                           ->paginate($request->per_page ?? Utilities::$per_page_max);

        return $list;

    }

    public function index() {

        return view("System/general/Assets/assets_management/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) {

        //

    }

    public function show($record) {

        //

    }

    public function edit($record) {

        //

    }

    public function update(Request $request, $id) {

        //

    }

    public function destroy($record) {

        //

    }

    public function assignAssetToBranch(AssignAssetToBranchRequest $request) {

        $userAuth = Auth::user();

        $company = $userAuth->company;

        $information = [
            "success" => [
                "counter" => 0,
                "data" => []
            ],
            "error" => [
                "counter" => 0,
                "data" => []
            ]
        ];

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        DB::transaction(function() use($request, $userAuth, $company, $branch, &$information) {

            foreach($request->branch_assets as $record) {

                // To insert or update
                $branchAsset = BranchAsset::where("branch_id", $branch->id)
                                          ->where("asset_id", $record["asset_id"])
                                          ->first();

                if(!Utilities::isDefined($branchAsset)) {

                    $branchAsset = new BranchAsset();
                    $branchAsset->branch_id         = $branch->id;
                    $branchAsset->asset_id          = $record["asset_id"];
                    $branchAsset->currency_id       = $company->currency_id;
                    $branchAsset->quantity          = $record["quantity"];
                    $branchAsset->acquisition_value = 0;
                    $branchAsset->acquisition_date  = null;
                    $branchAsset->note              = null;
                    $branchAsset->status            = "active";
                    $branchAsset->created_at        = now();
                    $branchAsset->created_by        = $userAuth->id ?? null;
                    $branchAsset->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"]["asset_id"] = $record["asset_id"];

                }else {

                    if(Utilities::isDefined($branchAsset) && in_array($branchAsset->status, ["retired"])) {

                        $branchAsset->currency_id       = $company->currency_id;
                        $branchAsset->quantity          = $record["quantity"];
                        $branchAsset->acquisition_value = 0;
                        $branchAsset->acquisition_date  = null;
                        $branchAsset->note              = null;
                        $branchAsset->status            = "active";
                        $branchAsset->updated_at        = now();
                        $branchAsset->updated_by        = $userAuth->id ?? null;
                        $branchAsset->save();

                        $information["success"]["counter"]++;
                        $information["success"]["data"]["asset_id"] = $record["asset_id"];

                    }else {

                        $information["error"]["counter"]++;
                        $information["error"]["data"]["asset_id"] = $record["asset_id"];

                    }

                }

            }

        });

        $bool = $information["success"]["counter"] > 0;
        $msg  = $bool ? "Activo asociado a la sucursal correctamente." : "No se ha podido asociar el activo a la sucursal.";

        return response()->json(["bool" => $bool, "msg" => $msg, "information" => $information], 200);

    }

    public function unassignAssetFromBranch(UnassignAssetFromBranchRequest $request) {

        $userAuth = Auth::user();

        $information = [
            "success" => [
                "counter" => 0,
                "data" => []
            ],
            "error" => [
                "counter" => 0,
                "data" => []
            ]
        ];

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        DB::transaction(function() use($request, $userAuth, $branch, &$information) {

            foreach($request->branch_assets as $record) {

                // To update, in status active and maintenance
                $branchAsset = BranchAsset::where("id", $record["id"])
                                          ->where("branch_id", $branch->id)
                                          ->where("asset_id", $record["asset_id"])
                                          ->whereIn("status", ["active", "maintenance"])
                                          ->first();

                if(Utilities::isDefined($branchAsset)) {

                    $branchAsset->status     = "retired";
                    $branchAsset->updated_at = now();
                    $branchAsset->updated_by = $userAuth->id ?? null;
                    $branchAsset->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"]["asset_id"] = $record["asset_id"];

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"]["asset_id"] = $record["asset_id"];

                }

            }

        });

        $bool = $information["success"]["counter"] > 0;
        $msg  = $bool ? "Activo desasociado a la sucursal correctamente." : "No se ha podido desasociar el activo a la sucursal.";

        return response()->json(["bool" => $bool, "msg" => $msg, "information" => $information], 200);

    }

    public function assetInBranch(Request $request) {

        $userAuth = Auth::user();

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        $branchAsset = BranchAsset::where("id", $request->id)
                                  ->where("branch_id", $branch->id)
                                  ->where("asset_id", $request->asset_id)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(Utilities::isDefined($branchAsset)) {

            DB::transaction(function() use($request, $userAuth, &$branchAsset) {

                $branchAsset->quantity          = $request->quantity ?? 0;
                $branchAsset->acquisition_value = $request->acquisition_value ?? 0;
                $branchAsset->acquisition_date  = $request->acquisition_date ?? null;
                $branchAsset->note              = $request->note ?? null;
                // $branchAsset->status            = $request->status;
                $branchAsset->updated_at        = now();
                $branchAsset->updated_by        = $userAuth->id ?? null;
                $branchAsset->save();

            });

        }

        $bool = Utilities::isDefined($branchAsset);
        $msg  = $bool ? "Activo editado correctamente." : "No se ha podido editar el activo.";

        return response()->json(["bool" => $bool, "msg" => $msg, "branchAsset" => $branchAsset], 200);

    }

    public function getAssetAssignments(Request $request) {

        $userAuth = Auth::user();

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        $assignments = AssetAssignment::where("branch_id", $branch->id)
                                      ->where("asset_id", $request->asset_id)
                                      ->whereIn("status", ["active", "maintenance"])
                                      ->with(["user"])
                                      ->get();

        $bool = count($assignments) > 0;
        $msg  = $bool ? "Información encontrada." : "Sin resultados.";

        return response()->json(["bool" => $bool, "msg" => $msg, "assignments" => $assignments], 200);

    }

    public function assignToUser(Request $request) {

        $userAuth = Auth::user();

        $information = [
            "success" => [
                "counter" => 0,
                "data" => []
            ],
            "error" => [
                "counter" => 0,
                "data" => []
            ]
        ];

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        $branchAsset = BranchAsset::where("id", $request->branch_asset_id)
                                  ->where("branch_id", $branch->id)
                                  ->where("asset_id", $request->asset_id)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(!Utilities::isDefined($branchAsset)) {

            return response()->json(["bool" => false, "msg" => "El activo asociado en la sucursal no ha sido encontrado."], 200);

        }

        $assetQuantity = floatval($branchAsset->quantity);
        $totalQuantity = array_reduce($request->assignments, function($acumulator, $currentValue) { return $acumulator + floatval($currentValue["quantity"]); }, 0);

        if($totalQuantity > $assetQuantity) {

            return response()->json(["bool" => false, "msg" => "La suma de las cantidades asignadas debe ser menor o igual a la cantidad total del activo."], 200);

        }

        DB::transaction(function() use($request, $userAuth, $branchAsset, &$information) {

            foreach($request->assignments as $record) {

                $data = [
                    "user_id"     => $record["user_id"],
                    "branch_id"   => $branchAsset->branch_id,
                    "asset_id"    => $branchAsset->asset_id,
                    "currency_id" => $branchAsset->currency_id,
                    "quantity"    => $record["quantity"] ?? 0,
                    "acquisition_value" => 0,
                    "acquisition_date"  => null,
                    "note"       => null,
                    "status"     => "active",
                    "updated_at" => now(),
                    "updated_by" => $userAuth->id
                ];

                if(is_numeric($record["id"])) {

                    $assetAssignment = "check";

                }else {

                    $data["created_at"] = now();
                    $data["created_by"] = $userAuth->id;

                    $assetAssignment = AssetAssignment::create($data);

                }

                if(Utilities::isDefined($assetAssignment)) {

                    $information["success"]["counter"]++;
                    $information["success"]["data"]["asset_id"] = $branchAsset->asset_id;

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"]["asset_id"] = $branchAsset->asset_id;

                }

            }

        });

        $bool = $information["success"]["counter"] > 0;
        $msg  = $bool ? "Activo asignado a los colaboradores correctamente." : "No se ha podido asignar el activo a los colaboradores.";

        return response()->json(["bool" => $bool, "msg" => $msg, "information" => $information], 200);

    }

    public function unassignToUser(Request $request) {

        $userAuth = Auth::user();

        $information = [
            "success" => [
                "counter" => 0,
                "data" => []
            ],
            "error" => [
                "counter" => 0,
                "data" => []
            ]
        ];

        $branch = Branch::where("id", $request->branch_id)
                        ->where("company_id", $userAuth->company_id)
                        ->first();

        if(!Utilities::isDefined($branch)) {

            return response()->json(["bool" => false, "msg" => "La sucursal no ha sido encontrada."], 200);

        }

        $branchAsset = BranchAsset::where("id", $request->branch_asset_id)
                                  ->where("branch_id", $branch->id)
                                  ->where("asset_id", $request->asset_id)
                                  ->whereIn("status", ["active", "maintenance"])
                                  ->first();

        if(!Utilities::isDefined($branchAsset)) {

            return response()->json(["bool" => false, "msg" => "El activo asociado en la sucursal no ha sido encontrado."], 200);

        }

        DB::transaction(function() use($request, $userAuth, $branchAsset, &$information) {

            foreach($request->assignments as $record) {

                $assetAssignment = AssetAssignment::where("id", $record["id"])
                                                  ->where("user_id", $record["user_id"])
                                                  ->where("branch_id", $branchAsset->branch_id)
                                                  ->where("asset_id", $branchAsset->asset_id)
                                                  ->whereIn("status", ["active", "maintenance"])
                                                  ->first();

                if(Utilities::isDefined($assetAssignment)) {

                    $assetAssignment->status     = "retired";
                    $assetAssignment->updated_at = now();
                    $assetAssignment->updated_by = $userAuth->id ?? null;
                    $assetAssignment->save();

                    $information["success"]["counter"]++;
                    $information["success"]["data"]["asset_id"] = $branchAsset->asset_id;

                }else {

                    $information["error"]["counter"]++;
                    $information["error"]["data"]["asset_id"] = $branchAsset->asset_id;

                }

            }

        });

        $bool = $information["success"]["counter"] > 0;
        $msg  = $bool ? "Activo retirado del colaborador correctamente." : "No se ha podido retirar el activo de los colaborador.";

        return response()->json(["bool" => $bool, "msg" => $msg, "information" => $information], 200);

    }

}
