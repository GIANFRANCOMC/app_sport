<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Assets\Assets\{StoreAssetRequest, UpdateAssetRequest};
use App\Models\System\Assets\{Asset};

class AssetController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->assets = new stdClass();
            $config->assets->statuses = Asset::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = Asset::when(Utilities::isDefined($request->filter_by), function($query) use($request) {

                            $filter = Utilities::getWordSearch($request->word);

                            if(in_array($request->filter_by, ["all"])) {

                                $query->where(function($query) use($request, $filter) {

                                    $query->where("internal_code", "like", $filter)
                                          ->orWhere("name", "like", $filter)
                                          ->orWhere("description", "like", $filter);

                                });

                            }else if(in_array($request->filter_by, ["internal_code", "name", "description"])) {

                                $query->where(function($query) use($request, $filter) {

                                    $query->where($request->filter_by, "like", $filter);

                                });

                            }

                      })
                      ->where("company_id", $userAuth->company_id)
                      ->orderBy("name", "ASC")
                      ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/assets/main");

    }

    public function create() {

        //

    }

    public function store(StoreAssetRequest $request) {

        $userAuth = Auth::user();

        $asset = null;

        $assetExists = Asset::where("company_id", $userAuth->company_id)
                            ->where("internal_code", $request->internal_code)
                            ->exists();

        if($assetExists) {

            return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

        }

        DB::transaction(function() use($request, $userAuth, &$asset) {

            $asset = new Asset();
            $asset->company_id    = $userAuth->company_id;
            $asset->internal_code = $request->internal_code;
            $asset->name          = $request->name;
            $asset->description   = $request->description ?? "";
            $asset->status        = $request->status;
            $asset->created_at    = now();
            $asset->created_by    = $userAuth->id ?? null;
            $asset->save();

        });

        $bool = Utilities::isDefined($asset);
        $msg  = $bool ? "Activo creado correctamente." : "No se ha podido crear el activo.";

        return response()->json(["bool" => $bool, "msg" => $msg, "asset" => $asset], 200);

    }

    public function show(Asset $record) {

        //

    }

    public function edit(Asset $record) {

        //

    }

    public function update(UpdateAssetRequest $request, $id) {

        $userAuth = Auth::user();

        $asset = Asset::where("id", $id)
                      ->where("company_id", $userAuth->company_id)
                      ->first();

        if(Utilities::isDefined($asset)) {

            $assetExists = Asset::where("company_id", $userAuth->company_id)
                                ->where("internal_code", $request->internal_code)
                                ->whereNot("id", $asset->id)
                                ->exists();

            if($assetExists) {

                return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

            }

            DB::transaction(function() use($request, $userAuth, &$asset) {

                $asset->internal_code = $request->internal_code;
                $asset->name          = $request->name;
                $asset->description   = $request->description ?? "";
                $asset->status        = $request->status;
                $asset->updated_at    = now();
                $asset->updated_by    = $userAuth->id ?? null;
                $asset->save();

            });

        }

        $bool = Utilities::isDefined($asset);
        $msg  = $bool ? "Activo editado correctamente." : "No se ha podido editar el activo.";

        return response()->json(["bool" => $bool, "msg" => $msg, "asset" => $asset], 200);

    }

    public function destroy(Asset $record) {

        //

    }

}
