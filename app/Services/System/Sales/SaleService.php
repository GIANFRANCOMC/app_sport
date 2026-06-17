<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use stdClass;

use App\Models\System\Customers\Subscription;
use App\Models\System\Sales\{SaleBody, SaleHeader, SalePayment, SaleTax};
use App\Models\System\Warehouses\{InventoryMovement, Warehouse};
use App\Services\System\Finance\CommercialDocumentSettlementService;
use App\Services\System\Organizations\Companies\CompanySettingService;
use App\Services\System\Warehouses\Inventory\InventoryMovementService;

/**
 * Service class for managing Sale operations
 * Handles business logic for creating, updating, and canceling sales
 */
class SaleService {

    /**
     * Translation namespace for sale module
     */
    private const TRANSLATION_NAMESPACE = "System.Sales.sale";

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Calculate total from sale details
     *
     * @param array $details Sale details
     * @return float
     */
    private static function calculateTotal(array $details): float {

        return array_reduce($details, function($carry, $detail) {

            return $carry + Utilities::round(floatval($detail["quantity"]) * floatval($detail["price"]));

        }, 0);

    }

    private static function calculateAdditionalTaxBase(array $details): float {

        return array_reduce($details, function($carry, $detail) {

            $priceIncludesTax = filter_var($detail["price_includes_tax"] ?? true, FILTER_VALIDATE_BOOL);

            if($priceIncludesTax) {

                return $carry;

            }

            return $carry + Utilities::round(floatval($detail["quantity"]) * floatval($detail["price"]));

        }, 0);

    }

    /**
     * Prepare sale body extras for subscription
     *
     * @param array $detail Sale detail
     * @return stdClass
     */
    private static function prepareSubscriptionExtras(array $detail): stdClass {

        $extras = new stdClass();

        if(isset($detail["extras"])) {

            $extras->duration_type  = $detail["extras"]["duration_type"] ?? null;
            $extras->duration_value = $detail["extras"]["duration_value"] ?? null;
            $extras->start_date     = isset($detail["extras"]["start_date"]) ? str_replace("T", " ", $detail["extras"]["start_date"]) : null;
            $extras->end_date       = isset($detail["extras"]["end_date"]) ? str_replace("T", " ", $detail["extras"]["end_date"]) : null;
            $extras->set_end_of_day = $detail["extras"]["set_end_of_day"] ?? false;
            $extras->force          = $detail["extras"]["force"] ?? true;
            $extras->observation    = $detail["extras"]["observation"] ?? "";

        }

        return $extras;

    }

    /**
     * Create sale body from detail
     *
     * @param SaleHeader $saleHeader Sale header instance
     * @param array $detail Sale detail data
     * @param int $userId User ID
     * @return SaleBody
     */
    private static function createSaleBody(SaleHeader $saleHeader, array $detail, int $userId): SaleBody {

        $extras = new stdClass();

        if(in_array($detail["type"], ["subscription"])) {

            $extras = self::prepareSubscriptionExtras($detail);

        }

        $saleBody = new SaleBody();
        $saleBody->sale_header_id = $saleHeader->id;
        $saleBody->item_id        = $detail["item_id"];
        $saleBody->currency_id    = $detail["currency_id"];
        $saleBody->name           = $detail["name"];
        $saleBody->quantity       = $detail["quantity"];
        $saleBody->price          = $detail["price"];
        $saleBody->price_includes_tax = filter_var($detail["price_includes_tax"] ?? true, FILTER_VALIDATE_BOOL);
        $saleBody->total          = Utilities::round((floatval($saleBody->quantity) * floatval($saleBody->price)));
        $saleBody->customer_id    = $saleHeader->holder_id;
        $saleBody->type           = $detail["type"];
        $saleBody->observation    = $detail["observation"] ?? "";
        $saleBody->extras         = json_encode($extras);
        $saleBody->status         = "active";
        $saleBody->created_at     = now();
        $saleBody->created_by     = $userId;
        $saleBody->save();

        return $saleBody;

    }

    /**
     * Update warehouse inventory for product sale
     *
     * @param Warehouse $warehouse Warehouse instance
     * @param SaleBody $saleBody Sale body instance
     * @param int $userId User ID
     * @return void
     */
    private static function updateWarehouseInventory(Warehouse $warehouse, SaleBody $saleBody, int $userId): void {

        if(!in_array($saleBody->type, ["product"])) {

            return;

        }

        InventoryMovementService::apply([
            "company_id"     => (int) $warehouse->branch->company_id,
            "warehouse_id"   => (int) $warehouse->id,
            "item_id"        => (int) $saleBody->item_id,
            "user_id"        => $userId,
            "movement_type"  => InventoryMovementService::TYPE_EXIT,
            "origin_type"    => InventoryMovementService::ORIGIN_SALE,
            "origin_id"      => (int) $saleBody->id,
            "quantity"       => (float) $saleBody->quantity,
            "reason"         => "Salida generada por venta.",
            "allow_negative" => true,
            "metadata"       => [
                "sale_header_id" => (int) $saleBody->sale_header_id
            ]
        ]);

    }

    /**
     * Create subscription from sale body
     *
     * @param SaleHeader $saleHeader Sale header instance
     * @param SaleBody $saleBody Sale body instance
     * @param array $detail Sale detail data
     * @param int $companyId Company ID
     * @param int $branchId Branch ID
     * @param int $userId User ID
     * @return Subscription|null
     */
    private static function createSubscription(SaleHeader $saleHeader, SaleBody $saleBody, array $detail, int $companyId, int $branchId, int $userId): ?Subscription {

        if(!in_array($saleBody->type, ["subscription"])) {

            return null;

        }

        $extras = self::prepareSubscriptionExtras($detail);

        $subscription = new Subscription();
        $subscription->company_id              = $companyId;
        $subscription->branch_id              = $branchId;
        $subscription->sale_header_id          = $saleHeader->id;
        $subscription->sale_body_id             = $saleBody->id;
        $subscription->customer_id             = $saleHeader->holder_id;
        $subscription->duration_type           = $extras->duration_type;
        $subscription->duration_value          = $extras->duration_value;
        $subscription->start_date              = $extras->start_date;
        $subscription->end_date                = $extras->end_date;
        $subscription->set_end_of_day          = $extras->set_end_of_day;
        $subscription->force                   = $extras->force;
        $subscription->attendance_limit_per_day = 1;
        $subscription->observation             = $extras->observation;
        $subscription->motive                  = null;
        $subscription->type                    = "sale";
        $subscription->status                  = "active";
        $subscription->created_at              = now();
        $subscription->created_by              = $userId;
        $subscription->save();

        return $subscription;

    }

    /**
     * Create a new sale
     *
     * @param array $data Sale data from request
     * @param int|null $userId User ID creating the sale
     * @return SaleHeader|null Created sale header instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?SaleHeader {

        $saleHeader = null;

        DB::transaction(function() use($data, $userId, &$saleHeader) {

            $userAuth  = Auth::user();
            $userId    = $userId ?? $userAuth->id;
            $companyId = $userAuth->company_id;

            // Validate warehouse exists
            $warehouse = Warehouse::where("branch_id", $data["branch_id"])->first();

            if(!$warehouse) {

                throw new Exception("La sucursal seleccionada no cuenta con almacén.");

            }

            // Get new sequential number
            $newSequential = SaleHeader::getNewSequential($data["serie_id"]);

            if($newSequential <= 0) {

                throw new Exception("No se pudo generar el número secuencial.");

            }

            // Calculate totals
            $subtotal = self::calculateTotal($data["details"]);
            $additionalTaxBase = self::calculateAdditionalTaxBase($data["details"]);
            $taxLines = CommercialDocumentSettlementService::taxes(
                (int) $companyId,
                "sale",
                (float) $additionalTaxBase,
                (int) $userId
            );
            $taxTotal = Utilities::round((float) $taxLines->sum("amount"));
            $total = Utilities::round($subtotal + $taxTotal);
            $paymentLines = CommercialDocumentSettlementService::payments(
                (int) $companyId,
                "sale",
                (float) $total,
                $data["payments"] ?? [],
                (int) $userId
            );

            // Create sale header
            $saleHeader = new SaleHeader();
            $saleHeader->serie_id    = $data["serie_id"];
            $saleHeader->sequential  = $newSequential;
            $saleHeader->holder_id   = $data["holder_id"];
            $saleHeader->seller_id   = $userId;
            $saleHeader->currency_id = $data["currency_id"];
            $saleHeader->issue_date  = $data["issue_date"];
            $saleHeader->subtotal    = $subtotal;
            $saleHeader->tax         = $taxTotal;
            $saleHeader->total       = $total;
            $saleHeader->observation = $data["observation"] ?? "";
            $saleHeader->status      = "active";
            $saleHeader->created_at  = now();
            $saleHeader->created_by  = $userId;
            $saleHeader->save();

            if($taxLines->isNotEmpty()) {

                SaleTax::insert($taxLines
                    ->map(fn($tax) => ["sale_header_id" => $saleHeader->id] + $tax)
                    ->all());

            }

            if($paymentLines->isNotEmpty()) {

                SalePayment::insert($paymentLines
                    ->map(fn($payment) => ["sale_header_id" => $saleHeader->id] + $payment)
                    ->all());

            }

            // Create sale bodies and process details
            foreach($data["details"] as $detail) {

                $saleBody = self::createSaleBody($saleHeader, $detail, $userId);

                // Update warehouse inventory for products
                self::updateWarehouseInventory($warehouse, $saleBody, $userId);

                // Create subscription for subscription items
                self::createSubscription($saleHeader, $saleBody, $detail, $companyId, $data["branch_id"], $userId);

            }

        });

        return $saleHeader;

    }

    /**
     * Cancel a sale
     *
     * @param SaleHeader $saleHeader Sale header instance
     * @param int|null $userId User ID canceling the sale
     * @return SaleHeader Updated sale header instance
     * @throws Exception
     */
    public static function cancel(SaleHeader $saleHeader, ?int $userId = null): SaleHeader {

        $stockRestored = false;
        $restoreStockPolicyEnabled = false;

        DB::transaction(function() use(
            $saleHeader,
            $userId,
            &$stockRestored,
            &$restoreStockPolicyEnabled
        ) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;
            $companyId = (int) $userAuth->company_id;
            $restoreStockPolicyEnabled = (bool) CompanySettingService::value(
                $companyId,
                CompanySettingService::INVENTORY_POLICIES,
                "restore_stock_on_sale_cancellation",
                false
            );

            if(!in_array($saleHeader->status, ["active"])) {

                throw new Exception("La venta no puede ser anulada.");

            }

            $saleHeader->loadMissing(
                $restoreStockPolicyEnabled
                    ? ["serie.branch.warehouses", "allPositions"]
                    : ["allPositions"]
            );

            $productPositions = $saleHeader->allPositions
                ->where("type", "product");
            $fallbackWarehouse = $restoreStockPolicyEnabled && $productPositions->isNotEmpty()
                ? $saleHeader->serie?->branch?->warehouses?->first()
                : null;

            $saleMovements = $restoreStockPolicyEnabled && $productPositions->isNotEmpty()
                ? InventoryMovement::query()
                    ->where("company_id", $companyId)
                    ->where("origin_type", InventoryMovementService::ORIGIN_SALE)
                    ->whereIn("origin_id", $productPositions->pluck("id"))
                    ->orderByDesc("id")
                    ->get(["id", "warehouse_id", "origin_id", "unit_cost"])
                    ->unique("origin_id")
                    ->keyBy("origin_id")
                : collect();

            if($restoreStockPolicyEnabled
                && $productPositions->isNotEmpty()
                && !$fallbackWarehouse
                && $saleMovements->count() !== $productPositions->count()) {

                throw new Exception("No se encontró el almacén asociado a la venta.");

            }

            if($restoreStockPolicyEnabled && $productPositions->isNotEmpty()) {

                foreach($productPositions as $saleBody) {

                    $warehouseId = (int) (
                        $saleMovements->get($saleBody->id)?->warehouse_id
                        ?? $fallbackWarehouse?->id
                        ?? 0
                    );

                    if($warehouseId <= 0) {

                        throw new Exception("No se encontró el almacén original de uno de los productos.");

                    }

                    InventoryMovementService::apply([
                        "company_id"    => $companyId,
                        "warehouse_id"  => $warehouseId,
                        "item_id"       => (int) $saleBody->item_id,
                        "user_id"       => $userId,
                        "movement_type" => InventoryMovementService::TYPE_ENTRY,
                        "origin_type"   => InventoryMovementService::ORIGIN_SALE_CANCELLATION,
                        "origin_id"     => (int) $saleBody->id,
                        "quantity"      => (float) $saleBody->quantity,
                        "unit_cost"     => (float) (
                            $saleMovements->get($saleBody->id)?->unit_cost ?? 0
                        ),
                        "reason"        => "Devolución automática por anulación de venta.",
                        "metadata"      => [
                            "sale_header_id" => (int) $saleHeader->id,
                            "automatic_return" => true
                        ]
                    ]);

                }

                $stockRestored = true;

            }

            // Update sale header
            $saleHeader->status      = "canceled";
            $saleHeader->updated_at  = now();
            $saleHeader->updated_by  = $userId;
            $saleHeader->canceled_at = now();
            $saleHeader->canceled_by = $userId;
            $saleHeader->save();

            // Cancel sale bodies
            SaleBody::where("sale_header_id", $saleHeader->id)
                    ->whereIn("status", ["active"])
                    ->update([
                        "status"      => "canceled",
                        "updated_at"  => now(),
                        "updated_by"  => $userId,
                        "canceled_at" => now(),
                        "canceled_by" => $userId
                    ]);

            // Cancel subscriptions
            $motive = "Por la anulación de la venta.";

            Subscription::where("company_id", $companyId)
                       ->where("sale_header_id", $saleHeader->id)
                       ->whereIn("type", ["sale"])
                       ->whereIn("status", ["active"])
                       ->update([
                           "motive"     => $motive,
                           "status"     => "canceled",
                           "updated_at" => now(),
                           "updated_by" => $userId,
                           "canceled_at" => now(),
                           "canceled_by" => $userId
                       ]);

        });

        $sale = $saleHeader->fresh();
        $sale->setAttribute("restore_stock_policy_enabled", $restoreStockPolicyEnabled);
        $sale->setAttribute("stock_restored_on_cancellation", $stockRestored);

        return $sale;

    }

    /**
     * Find sale header by ID
     *
     * @param int $id Sale header ID
     * @return SaleHeader|null
     */
    public static function findById(int $id): ?SaleHeader {

        return SaleHeader::find($id);

    }

    /**
     * Get paginated list of sales
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15) {

        $branches = \App\Models\System\Organizations\Branch::where("company_id", $companyId)
                                                           ->with(["series"])
                                                           ->get();

        $serieIds = $branches->pluck("series.*.id")->flatten();

        $query = SaleHeader::whereIn("serie_id", $serieIds)
                           ->with(["serie.documentType", "holder", "currency", "taxes", "payments"]);

        // Apply filters
        self::applyFilters($query, $filters);

        // Apply ordering
        $query->orderBy("id", "DESC");

        return $query->paginate($perPage);

    }

    /**
     * Apply filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applyFilters($query, array $filters): void {

        if(Utilities::isDefined($filters["serie_id"])) {

            $query->where("serie_id", $filters["serie_id"]);

        }

        if(Utilities::isDefined($filters["sequential"])) {

            $query->where("sequential", $filters["sequential"]);

        }

        if(Utilities::isDefined($filters["issue_date"])) {

            $query->where("issue_date", $filters["issue_date"]);

        }

        if(Utilities::isDefined($filters["holder_id"])) {

            $query->where("holder_id", $filters["holder_id"]);

        }

        if(Utilities::isDefined($filters["status"])) {

            $query->where("status", $filters["status"]);

        }

    }

}

