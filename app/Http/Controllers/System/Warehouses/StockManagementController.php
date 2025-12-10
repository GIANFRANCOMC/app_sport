<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Models\System\Catalogs\{Item};
use App\Models\System\Warehouses\{Warehouse, WarehouseItem};

class StockManagementController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->warehouses = new stdClass();
            $config->warehouses->records = Warehouse::getAll("stock_management");

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = Item::where("company_id", $userAuth->company_id)
                    ->whereIn("type", ["product"])
                    // ->whereIn("status", ["active"])
                    ->withSum(["warehouseItems as stock_quantity" => function($query) use($request) {

                        $query->where("warehouse_id", $request->warehouse_id);

                    }], "quantity")
                    ->orderBy("name", "ASC")
                    ->paginate($request->per_page ?? Utilities::$per_page_max);

        return $list;

    }

    public function index() {

        return view("System/general/stocks_management/main");

    }

    public function create() {

        //

    }

    public function store(Request $request) {

        $userAuth = Auth::user();

        $warehouseExists = Warehouse::where("id", $request->warehouse_id)
                                    ->whereHas("branch", function($query) use($userAuth) {

                                        $query->where("company_id", $userAuth->company_id);

                                    })
                                    // ->whereIn("status", ["active"])
                                    ->exists();

        if(!$warehouseExists) {

            return response()->json(["bool" => false, "msg" => "El almacén seleccionado no se encuentra disponible."], 200);

        }

        DB::transaction(function() use($request, $userAuth) {

            foreach($request->items as $item) {

                $warehouseItem = WarehouseItem::where("warehouse_id", $request->warehouse_id)
                                              ->where("item_id", $item["id"])
                                              ->first();

                if($warehouseItem) {

                    $warehouseItem->update([
                        "quantity"     => floatval($item["stock_quantity"]),
                        "status"       => "active",
                        "updated_at"   => now(),
                        "updated_by"   => $userAuth->id
                    ]);

                }else {

                    WarehouseItem::create([
                        "warehouse_id" => $request->warehouse_id,
                        "item_id"      => $item["id"],
                        "quantity"     => floatval($item["stock_quantity"]),
                        "status"       => "active",
                        "created_at"   => now(),
                        "created_by"   => $userAuth->id
                    ]);

                }

            }

        });

        $bool = true;
        $msg  = $bool ? "Stock actualizado correctamente." : "No se ha podido actualizar el stock.";

        return response()->json(["bool" => $bool, "msg" => $msg], 200);

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

}
