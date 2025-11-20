<?php

namespace App\Http\Controllers\System;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Http\Requests\System\Sales\{CancelSaleRequest, StoreSaleRequest, UpdateSaleRequest};
use App\Models\System\Catalogs\{Item};
use App\Models\System\Customers\{Customer, Subscription};
use App\Models\System\General\{Currency, IdentityDocumentType};
use App\Models\System\Organizations\{Branch};
use App\Models\System\Sales\{SaleBody, SaleHeader};
use App\Models\System\Warehouses\{Warehouse, WarehouseItem};

class SaleController extends Controller {

    public function initParams(Request $request) {

        $initParams = new stdClass();

        $config = new stdClass();

        $page = $request->page ?? "";

        if(in_array($page, ["list"])) {

            $config->branches = new stdClass();
            $config->branches->records = Branch::getAll();

            $config->customers = new stdClass();
            $config->customers->records = Customer::getAll();

            $config->salesHeader = new stdClass();
            $config->salesHeader->statuses = SaleHeader::getStatuses();

        }else if(in_array($page, ["main"])) {

            $config->branches = new stdClass();
            $config->branches->records = Branch::getAll("sale");

            $config->currencies = new stdClass();
            $config->currencies->records = Currency::get();

            $config->customers = new stdClass();
            $config->customers->records = Customer::getAll("sale");

            $config->identityDocumentTypes = new stdClass();
            $config->identityDocumentTypes->records = IdentityDocumentType::getAll("sale");

            $config->items = new stdClass();
            $config->items->durationTypes = Item::getDurationTypes();
            $config->items->records = Item::getAll("sale");

            $config->salesHeader = new stdClass();
            $config->salesHeader->statuses = SaleHeader::getStatuses();

        }

        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

    public function list(Request $request) {

        $userAuth = Auth::user();

        $branches = Branch::where("company_id", $userAuth->company_id)
                          ->with(["series"])
                          ->get();

        $serieIds = $branches->pluck("series.*.id")->flatten();

        $list = SaleHeader::when(Utilities::isDefined($request->serie_id), function($query) use($request) {

                                $query->where("serie_id", $request->serie_id);

                           })
                           ->when(Utilities::isDefined($request->sequential), function($query) use($request) {

                                $query->where("sequential", $request->sequential);

                           })
                           ->when(Utilities::isDefined($request->issue_date), function($query) use($request) {

                                $query->where("issue_date", $request->issue_date);

                           })
                           ->when(Utilities::isDefined($request->holder_id), function($query) use($request) {

                                $query->where("holder_id", $request->holder_id);

                           })
                           ->when(Utilities::isDefined($request->status), function($query) use($request) {

                                $query->where("status", $request->status);

                           })
                           ->whereIn("serie_id", $serieIds)
                           ->orderBy("id", "DESC")
                           ->with(["serie.documentType", "holder", "currency"])
                           ->paginate($request->per_page ?? Utilities::$per_page_default);

        return $list;

    }

    public function index() {

        return view("System/general/sales/list");

    }

    public function create() {

        return view("System/general/sales/main");

    }

    public function store(StoreSaleRequest $request) {

        $userAuth = Auth::user();

        $saleHeader = null;

        $warehouse = Warehouse::where("branch_id", $request->branch_id)
                              ->first();

        if(Utilities::isDefined($warehouse)) {

            DB::transaction(function() use($request, $userAuth, $warehouse, &$saleHeader) {

                $newSequential = SaleHeader::getNewSequential($request->serie_id);

                if($newSequential > 0) {

                    $total = array_reduce($request["details"], function($carry, $detail) {

                        return $carry + Utilities::round(floatval($detail["quantity"]) * floatval($detail["price"]));

                    }, 0);

                    $saleHeader = new SaleHeader();
                    $saleHeader->serie_id    = $request->serie_id;
                    $saleHeader->sequential  = $newSequential;
                    $saleHeader->holder_id   = $request->holder_id;
                    $saleHeader->seller_id   = $userAuth->id;
                    $saleHeader->currency_id = $request->currency_id;
                    $saleHeader->issue_date  = $request->issue_date;
                    $saleHeader->total       = $total;
                    $saleHeader->observation = $request->observation ?? "";
                    $saleHeader->status      = "active";
                    $saleHeader->created_at  = now();
                    $saleHeader->created_by  = $userAuth->id ?? null;
                    $saleHeader->save();

                    foreach($request["details"] as $detail) {

                        $extras = new stdClass();

                        if(in_array($detail["type"], ["subscription"])) {

                            $extras->duration_type  = $detail["extras"]["duration_type"];
                            $extras->duration_value = $detail["extras"]["duration_value"];
                            $extras->start_date     = str_replace("T", " ", $detail["extras"]["start_date"]);
                            $extras->end_date       = str_replace("T", " ", $detail["extras"]["end_date"]);
                            $extras->set_end_of_day = $detail["extras"]["set_end_of_day"] ?? false;
                            $extras->force          = $detail["extras"]["force"] ?? true;

                        }

                        $saleBody = new SaleBody();
                        $saleBody->sale_header_id = $saleHeader->id;
                        $saleBody->item_id        = $detail["item_id"];
                        $saleBody->currency_id    = $detail["currency_id"];
                        $saleBody->name           = $detail["name"];
                        $saleBody->quantity       = $detail["quantity"];
                        $saleBody->price          = $detail["price"];
                        $saleBody->total          = Utilities::round((floatval($saleBody->quantity) * floatval($saleBody->price)));
                        $saleBody->customer_id    = $saleHeader->holder_id;
                        $saleBody->type           = $detail["type"];
                        $saleBody->observation    = $detail["observation"] ?? "";
                        $saleBody->extras         = json_encode($extras);
                        $saleBody->status         = "active";
                        $saleBody->created_at     = now();
                        $saleBody->created_by     = $userAuth->id ?? null;
                        $saleBody->save();

                        $total += floatval($saleBody->total);

                        if(in_array($detail["type"], ["product"])) {

                            $warehouseItem = WarehouseItem::where("warehouse_id", $warehouse->id)
                                                          ->where("item_id", $saleBody->item_id)
                                                          ->first();

                            if($warehouseItem) {

                                $warehouseItem->update([
                                    "quantity"     => floatval($warehouseItem->quantity) - floatval($saleBody->quantity),
                                    "status"       => "active",
                                    "updated_at"   => now(),
                                    "updated_by"   => $userAuth->id
                                ]);

                            }else {

                                WarehouseItem::create([
                                    "warehouse_id" => $request->warehouse_id,
                                    "item_id"      => $saleBody->item_id,
                                    "quantity"     => floatval(0) - floatval($saleBody->quantity),
                                    "status"       => "active",
                                    "created_at"   => now(),
                                    "created_by"   => $userAuth->id
                                ]);

                            }

                        }else if(in_array($detail["type"], ["subscription"])) {

                            $extras->observation = $detail["extras"]["observation"] ?? "";

                            $subscription = new Subscription();
                            $subscription->company_id     = $userAuth->company_id;
                            $subscription->branch_id      = $request->branch_id;
                            $subscription->sale_header_id = $saleHeader->id;
                            $subscription->sale_body_id   = $saleBody->id;
                            $subscription->customer_id    = $saleHeader->holder_id;
                            $subscription->duration_type  = $extras->duration_type;
                            $subscription->duration_value = $extras->duration_value;
                            $subscription->start_date     = $extras->start_date;
                            $subscription->end_date       = $extras->end_date;
                            $subscription->set_end_of_day = $extras->set_end_of_day;
                            $subscription->force          = $extras->force;
                            $subscription->attendance_limit_per_day = 1;
                            $subscription->observation    = $extras->observation;
                            $subscription->motive         = null;
                            $subscription->type           = "sale";
                            $subscription->status         = "active";
                            $subscription->created_at     = now();
                            $subscription->created_by     = $userAuth->id ?? null;
                            $subscription->save();

                        }

                    }

                    // $saleHeader->total  = $total;
                    // $saleHeader->status = "active";
                    // $saleHeader->save();

                    // With
                    // $saleHeader->serie;
                    // $saleHeader->serie_sequential;

                }

            });

            $bool = Utilities::isDefined($saleHeader);
            $msg  = $bool ? "Venta creada correctamente." : "No se ha podido crear la venta.";

        }else {

            $bool = false;
            $msg  = "La sucursal seleccionada no cuenta con almacén.";

        }

        return response()->json(["bool" => $bool, "msg" => $msg, "sale" => $saleHeader], 200);

    }

    public function show(SaleHeader $record) {

        //

    }

    public function edit(SaleHeader $record) {

        //

    }

    public function update(UpdateSaleRequest $request, $id) {

        //

    }

    public function cancel(CancelSaleRequest $request, $id) {

        $userAuth = Auth::user();

        $saleHeader = SaleHeader::findOrFail($id);

        if(Utilities::isDefined($saleHeader) && in_array($saleHeader->status, ["active"])) {

            if($serie = $saleHeader->serie) {

                $branch = $serie->branch;

                if(Utilities::isDefined($branch) && $branch->company_id == $userAuth->company_id) {

                    $saleHeader->status      = "canceled";
                    $saleHeader->updated_at  = now();
                    $saleHeader->updated_by  = $userAuth->id ?? null;
                    $saleHeader->canceled_at = now();
                    $saleHeader->canceled_by = $userAuth->id ?? null;
                    $saleHeader->save();

                    $motive = "Por la anulación de la venta.";

                    SaleBody::where("sale_header_id", $saleHeader->id)
                            ->whereIn("status", ["active"])
                            ->update(["status" => "canceled",
                                      "updated_at" => now(),
                                      "updated_by" => $userAuth->id ?? null,
                                      "canceled_at" => now(),
                                      "canceled_by" => $userAuth->id ?? null]);

                    Subscription::where("company_id", $userAuth->company_id)
                                ->where("sale_header_id", $saleHeader->id)
                                ->whereIn("type", ["sale"])
                                ->whereIn("status", ["active"])
                                ->update(["motive" => $motive,
                                          "status" => "canceled",
                                          "updated_at" => now(),
                                          "updated_by" => $userAuth->id ?? null,
                                          "canceled_at" => now(),
                                          "canceled_by" => $userAuth->id ?? null]);

                }

            }

        }

        $bool = $saleHeader->wasChanged();
        $msg  = $bool ? "Venta anulada correctamente." : "No se ha podido anular la venta.";

        return response()->json(["bool" => $bool, "msg" => $msg, "sale" => $saleHeader], 200);

    }

    public function destroy(SaleHeader $record) {

        //

    }

}
