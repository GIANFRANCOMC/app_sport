<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Products\{StoreProductRequest, UpdateProductRequest};
use App\Models\System\Catalogs\{CategoryItem, Item};
use App\Models\System\General\{Category, Currency};
use App\Models\System\Organizations\{Branch};
use App\Models\System\Warehouses\{WarehouseItem};

class ProductController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["main"])) {

            $config->products = new stdClass();
            $config->products->statuses = Item::getStatuses();

            $config->categories = new stdClass();
            $config->categories->records = Category::getAll("product");

            $config->currencies = new stdClass();
            $config->currencies->records = Currency::get();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $list = Item::when(Utilities::isDefined($request->filter_by), function($query) use($request) {

                        $filter = Utilities::getWordSearch($request->word);

                        if(in_array($request->filter_by, ["all"])) {

                            $query->where(function($query) use($request, $filter) {

                                $query->where("internal_code", "like", $filter)
                                      ->orWhere("name", "like", $filter)
                                      ->orWhere("description", "like", $filter)
                                      ->orWhere("price", "like", $filter);

                            });

                        }else if(in_array($request->filter_by, ["internal_code", "name", "description", "price"])) {

                            $query->where(function($query) use($request, $filter) {

                                $query->where($request->filter_by, "like", $filter);

                            });

                        }

                    })
                    ->where("company_id", $userAuth->company_id)
                    ->whereIn("type", ["product"])
                    ->orderBy("name", "ASC")
                    ->with(["currency", "categoryItems"])
                    ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/products/main");

    }

    public function create() {

        //

    }

    public function store(StoreProductRequest $request) {

        $userAuth = Auth::user();

        $item = null;

        $itemExists = Item::where("company_id", $userAuth->company_id)
                          ->where("internal_code", $request->internal_code)
                          ->exists();

        if($itemExists) {

            return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

        }

        DB::transaction(function() use($request, $userAuth, &$item) {

            $item = new Item();
            $item->company_id    = $userAuth->company_id;
            $item->internal_code = $request->internal_code;
            $item->name          = $request->name;
            $item->description   = $request->description ?? "";
            $item->price         = $request->price;
            $item->min_price     = floatval($request->min_price) <= 0 ? null : $request->min_price;
            $item->max_price     = floatval($request->max_price) <= 0 ? null : $request->max_price;
            $item->currency_id   = $request->currency_id;
            $item->type          = "product";
            $item->see_my_web    = $request->see_my_web ?? false;
            $item->see_my_web_price = $item->see_my_web ? ($request->see_my_web_price ?? false) : false;
            $item->status        = $request->status;
            $item->created_at    = now();
            $item->created_by    = $userAuth->id ?? null;
            $item->save();

            // Warehouses
            $branches = Branch::getAll();

            foreach($branches as $branch) {

                foreach($branch->warehouses as $warehouse) {

                    $warehouseItem = new WarehouseItem();
                    $warehouseItem->warehouse_id = $warehouse->id;
                    $warehouseItem->item_id      = $item->id;
                    $warehouseItem->quantity     = 0;
                    $warehouseItem->status       = $request->status;
                    $warehouseItem->created_at   = now();
                    $warehouseItem->created_by   = $userAuth->id ?? null;
                    $warehouseItem->save();

                }

            }

            foreach($request->categories as $category) {

                CategoryItem::updateOrInsert(
                    [
                        "category_id" => $category["category_id"],
                        "item_id"     => $item->id
                    ],
                    [
                        "status"      => "active",
                        "updated_at"  => now(),
                        "updated_by"  => $userAuth->id ?? null
                    ]
                );

            }

        });

        $bool = Utilities::isDefined($item);
        $msg  = $bool ? "Producto creado correctamente." : "No se ha podido crear el producto.";

        return response()->json(["bool" => $bool, "msg" => $msg, "item" => $item], 200);

    }

    public function show(Item $item) {

        //

    }

    public function edit(Item $item) {

        //

    }

    public function update(UpdateProductRequest $request, $id) {

        $userAuth = Auth::user();

        $item = Item::where("id", $id)
                    ->where("company_id", $userAuth->company_id)
                    ->whereIn("type", ["product"])
                    ->first();

        if(Utilities::isDefined($item)) {

            $itemExists = Item::where("company_id", $userAuth->company_id)
                              ->where("internal_code", $request->internal_code)
                              ->whereNot("id", $item->id)
                              ->exists();

            if($itemExists) {

                return response()->json(["bool" => false, "msg" => "El código interno ya ha sido registrado."], 200);

            }

            DB::transaction(function() use($request, $userAuth, &$item) {

                $item->internal_code = $request->internal_code;
                $item->name          = $request->name;
                $item->description   = $request->description ?? "";
                $item->price         = $request->price;
                $item->min_price     = floatval($request->min_price) <= 0 ? null : $request->min_price;
                $item->max_price     = floatval($request->max_price) <= 0 ? null : $request->max_price;
                $item->currency_id   = $request->currency_id;
                $item->see_my_web    = $request->see_my_web ?? false;
                $item->see_my_web_price = $item->see_my_web ? ($request->see_my_web_price ?? false) : false;
                $item->status        = $request->status;
                $item->updated_at    = now();
                $item->updated_by    = $userAuth->id ?? null;
                $item->save();

                CategoryItem::where("item_id", $item->id)
                            ->where("status", "active")
                            ->update([
                                "status"     => "inactive",
                                "updated_at" => now(),
                                "updated_by" => $userAuth->id ?? null
                            ]);

                foreach($request->categories as $category) {

                    CategoryItem::updateOrInsert(
                        [
                            "category_id" => $category["category_id"],
                            "item_id"     => $item->id
                        ],
                        [
                            "status"      => "active",
                            "updated_at"  => now(),
                            "updated_by"  => $userAuth->id ?? null
                        ]
                    );

                }

            });

        }

        $bool = Utilities::isDefined($item);
        $msg  = $bool ? "Producto editado correctamente." : "No se ha podido editar el producto.";

        return response()->json(["bool" => $bool, "msg" => $msg, "item" => $item], 200);

    }

    public function destroy(Item $item) {

        //

    }

}
