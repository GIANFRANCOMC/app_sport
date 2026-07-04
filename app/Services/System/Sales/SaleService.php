<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\DB;
use stdClass;

use App\Models\System\Customers\Subscription;
use App\Models\System\Finance\{CashMovement, CashSession};
use App\Models\System\Organizations\{Serie, User};
use App\Models\System\Sales\{SaleBody, SaleHeader, SalePayment, SaleTax};
use App\Models\System\Warehouses\{InventoryMovement, Warehouse};
use App\Services\System\Finance\CommercialDocumentSettlementService;
use App\Services\System\Organizations\Companies\CompanySettingService;
use App\Services\System\Operations\ServiceOperationService;
use App\Services\System\Customers\Tracking\TrackingSubscriptionService;
use App\Services\System\Catalogs\Recipes\RecipeConsumptionService;
use App\Services\System\Organizations\{AccessScopeService};
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
    private const CORRELATIVE_ISSUED = "issued";
    private const CORRELATIVE_CANCELED = "canceled";

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

    /**
     * Prepare sale body extras for subscription
     *
     * @param array $detail Sale detail
     * @return stdClass
     */
    private static function prepareSubscriptionExtras(array $detail): stdClass {

        $extras = (object) ($detail["extras"] ?? []);

        if(isset($detail["extras"])) {

            $extras->duration_type  = $detail["extras"]["duration_type"] ?? null;
            $extras->duration_value = $detail["extras"]["duration_value"] ?? null;
            $extras->start_date     = isset($detail["extras"]["start_date"]) ? str_replace("T", " ", $detail["extras"]["start_date"]) : null;
            $extras->end_date       = isset($detail["extras"]["end_date"]) ? str_replace("T", " ", $detail["extras"]["end_date"]) : null;
            $extras->set_end_of_day = $detail["extras"]["set_end_of_day"] ?? false;
            $extras->force          = $detail["extras"]["force"] ?? false;
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
        $saleBody->company_id = $saleHeader->company_id;
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
    private static function updateWarehouseInventory(
        Warehouse $warehouse,
        SaleBody $saleBody,
        array $detail,
        int $userId
    ): void {

        $companyId = (int) $warehouse->branch->company_id;
        $allowNegativeStock = (bool) CompanySettingService::value(
            $companyId,
            CompanySettingService::INVENTORY_POLICIES,
            "allow_negative_stock_on_sale",
            false
        );

        if(RecipeConsumptionService::consume(
            $warehouse,
            $saleBody,
            $detail,
            $companyId,
            $userId,
            $allowNegativeStock
        )) {

            return;

        }

        if(!in_array($saleBody->type, ["product"])) {

            return;

        }

        InventoryMovementService::apply([
            "company_id"     => $companyId,
            "warehouse_id"   => (int) $warehouse->id,
            "item_id"        => (int) $saleBody->item_id,
            "user_id"        => $userId,
            "movement_type"  => InventoryMovementService::TYPE_EXIT,
            "origin_type"    => InventoryMovementService::ORIGIN_SALE,
            "origin_id"      => (int) $saleBody->id,
            "quantity"       => (float) $saleBody->quantity,
            "reason"         => "Salida generada por venta.",
            "allow_negative" => $allowNegativeStock,
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

        TrackingSubscriptionService::assertDatesAvailable(
            $companyId,
            $branchId,
            (int) $saleHeader->holder_id,
            (string) $extras->start_date,
            (string) $extras->end_date,
            (bool) $extras->force
        );

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
        $subscription->attendance_limit_per_day = max(1, (int) ($detail["attendance_limit_per_day"] ?? 1));
        $subscription->observation             = $extras->observation;
        $subscription->motive                  = null;
        $subscription->type                    = "sale";
        $subscription->status                  = "active";
        $subscription->created_at              = now();
        $subscription->created_by              = $userId;
        $subscription->save();

        return $subscription;

    }

    private static function resolveWarehouse(array $data, int $companyId): Warehouse {

        $warehouseQuery = Warehouse::query()
            ->with("branch")
            ->where("branch_id", $data["branch_id"])
            ->whereHas("branch", function($query) use($companyId) {

                $query->where("company_id", $companyId)
                      ->where("status", "active");

            })
            ->where("status", "active");

        if(Utilities::isDefined($data["warehouse_id"] ?? null)) {

            $warehouse = (clone $warehouseQuery)
                ->where("id", $data["warehouse_id"])
                ->first();

            if(!$warehouse) {

                throw new Exception("El almacén seleccionado no está activo o no pertenece a la sucursal de la venta.");

            }

            return $warehouse;

        }

        $warehouses = $warehouseQuery->get();

        if($warehouses->count() === 1) {

            return $warehouses->first();

        }

        if($warehouses->isEmpty()) {

            throw new Exception("La sucursal no tiene un almacén activo. Crea o activa un almacén antes de registrar la venta.");

        }

        throw new Exception("Selecciona el almacén que será afectado por la venta.");

    }

    private static function resolveCashSession(
        array $data,
        int $companyId,
        int $userId
    ): ?CashSession {

        $cashSessionId = (int) ($data["cash_session_id"] ?? 0);
        $required = (bool) CompanySettingService::value(
            $companyId,
            CompanySettingService::CASH,
            "require_open_session_on_sale",
            false
        );

        if($cashSessionId <= 0) {

            if($required) {

                throw new Exception("Abre una caja de la sucursal antes de registrar la venta.");

            }

            return null;

        }

        $session = CashSession::query()
            ->with("register")
            ->where("company_id", $companyId)
            ->where("branch_id", (int) $data["branch_id"])
            ->where("status", "open")
            ->find($cashSessionId);

        if(!$session
            || !AccessScopeService::canAccess(
                User::query()->where("company_id", $companyId)->findOrFail($userId),
                AccessScopeService::CASH_REGISTER,
                (int) $session->cash_register_id
            )) {

            throw new Exception("La caja seleccionada no está abierta, no pertenece a la sucursal o no está autorizada para el usuario.");

        }

        return $session;

    }

    private static function createCashMovements(SaleHeader $saleHeader, $paymentLines, int $companyId, int $branchId, int $userId): void {

        if(!Utilities::isDefined($saleHeader->cash_session_id) || $paymentLines->isEmpty()) {

            return;

        }

        CashMovement::insert($paymentLines
            ->map(function($payment) use($saleHeader, $companyId, $branchId, $userId) {

                return [
                    "company_id" => $companyId,
                    "branch_id" => $branchId,
                    "cash_session_id" => $saleHeader->cash_session_id,
                    "payment_method_id" => $payment["payment_method_id"] ?? null,
                    "user_id" => $userId,
                    "movement_type" => "sale",
                    "origin_type" => "sale",
                    "origin_id" => $saleHeader->id,
                    "amount" => $payment["amount"] ?? 0,
                    "reference" => $payment["reference"] ?? null,
                    "note" => $payment["note"] ?? null,
                    "occurred_at" => now(),
                    "status" => "active",
                    "created_at" => now(),
                    "created_by" => $userId
                ];

            })
            ->all());

    }

    private static function validateSerieBelongsToBranch(int $serieId, int $branchId): void {

        $exists = Serie::query()
                       ->where("id", $serieId)
                       ->where("branch_id", $branchId)
                       ->where("status", "active")
                       ->exists();

        if(!$exists) {

            $hasActiveSeries = Serie::query()
                ->where("branch_id", $branchId)
                ->where("status", "active")
                ->exists();

            if(!$hasActiveSeries) {

                throw new Exception("La sucursal no tiene una serie activa. Crea o activa una serie antes de registrar la venta.");

            }

            throw new Exception("El comprobante seleccionado no está activo o no pertenece a la sucursal de la venta.");

        }

    }

    private static function recordCorrelativeMovement(
        SaleHeader $saleHeader,
        string $action,
        int $companyId,
        int $userId,
        string $source = "sale"
    ): void {

        DB::table("series_correlative_movements")->insert([
            "company_id" => $companyId,
            "serie_id" => (int) $saleHeader->serie_id,
            "sale_header_id" => (int) $saleHeader->id,
            "user_id" => $userId,
            "sequential" => (int) $saleHeader->sequential,
            "action" => $action,
            "source" => in_array($source, ["sale", "pos"], true) ? $source : "sale",
            "note" => $action === self::CORRELATIVE_CANCELED
                ? "Correlativo conservado en el historial por anulación de la venta."
                : "Correlativo asignado al registrar la venta.",
            "metadata" => json_encode([
                "sale_status" => (string) $saleHeader->status
            ]),
            "occurred_at" => now(),
            "created_at" => now()
        ]);

    }

    /**
     * Create a new sale
     *
     * @param array $data Sale data from request
     * @param int $companyId Company that owns the sale
     * @param int $userId User ID creating the sale
     * @return SaleHeader|null Created sale header instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, int $companyId, int $userId): ?SaleHeader {

        $saleHeader = null;

        DB::transaction(function() use($data, $companyId, $userId, &$saleHeader) {

            $warehouse = self::resolveWarehouse($data, (int) $companyId);
            $cashSession = self::resolveCashSession($data, (int) $companyId, (int) $userId);
            self::validateSerieBelongsToBranch((int) $data["serie_id"], (int) $data["branch_id"]);

            // Get new sequential number
            $newSequential = SaleHeader::getNewSequential($data["serie_id"]);

            if($newSequential <= 0) {

                throw new Exception("No se pudo generar el número secuencial.");

            }

            // Calculate totals
            $grossSubtotal = self::calculateTotal($data["details"]);
            $selectedTaxIds = collect($data["taxes"] ?? [])
                ->pluck("tax_id")
                ->filter()
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $selectedTaxQuantities = collect($data["taxes"] ?? [])
                ->filter(fn($tax) => !empty($tax["tax_id"]))
                ->mapWithKeys(fn($tax) => [(int) $tax["tax_id"] => (float) ($tax["quantity"] ?? 1)])
                ->all();
            $taxLines = CommercialDocumentSettlementService::saleTaxes(
                (int) $companyId,
                $data["details"],
                (int) $userId,
                $selectedTaxIds,
                $selectedTaxQuantities
            );
            $taxTotal = Utilities::round((float) $taxLines->sum("amount"));
            $taxImpactTotal = Utilities::round((float) $taxLines->sum("_total_impact"));
            $includedTaxTotal = Utilities::round($taxTotal - $taxImpactTotal);
            $subtotal = Utilities::round($grossSubtotal - $includedTaxTotal);
            $total = Utilities::round($grossSubtotal + $taxImpactTotal);
            $paymentLines = CommercialDocumentSettlementService::payments(
                (int) $companyId,
                "sale",
                (float) $total,
                $data["payments"] ?? [],
                (int) $userId
            );

            // Create sale header
            $saleHeader = new SaleHeader();
            $saleHeader->company_id  = $companyId;
            $saleHeader->serie_id    = $data["serie_id"];
            $saleHeader->sequential  = $newSequential;
            $saleHeader->holder_id   = $data["holder_id"];
            $saleHeader->seller_id   = $userId;
            $saleHeader->currency_id = $data["currency_id"];
            $saleHeader->warehouse_id = $warehouse->id;
            $saleHeader->cash_session_id = $cashSession?->id;
            $saleHeader->issue_date  = $data["issue_date"];
            $saleHeader->subtotal    = $subtotal;
            $saleHeader->tax         = $taxTotal;
            $saleHeader->total       = $total;
            $saleHeader->observation = $data["observation"] ?? "";
            $saleHeader->status      = "active";
            $saleHeader->created_at  = now();
            $saleHeader->created_by  = $userId;
            $saleHeader->save();

            self::recordCorrelativeMovement(
                $saleHeader,
                self::CORRELATIVE_ISSUED,
                (int) $companyId,
                (int) $userId,
                (string) ($data["source_channel"] ?? "sale")
            );

            if($taxLines->isNotEmpty()) {

                SaleTax::insert($taxLines
                    ->map(function($tax) use($saleHeader) {

                        unset($tax["_total_impact"]);

                        return ["sale_header_id" => $saleHeader->id] + $tax;

                    })
                    ->all());

            }

            if($paymentLines->isNotEmpty()) {

                SalePayment::insert($paymentLines
                    ->map(fn($payment) => ["sale_header_id" => $saleHeader->id] + $payment)
                    ->all());

                self::createCashMovements($saleHeader, $paymentLines, (int) $companyId, (int) $data["branch_id"], (int) $userId);

            }

            // Create sale bodies and process details
            foreach($data["details"] as $detail) {

                $saleBody = self::createSaleBody($saleHeader, $detail, $userId);

                // Update warehouse inventory for products
                self::updateWarehouseInventory($warehouse, $saleBody, $detail, $userId);

                // Create subscription for subscription items
                self::createSubscription($saleHeader, $saleBody, $detail, $companyId, $data["branch_id"], $userId);

            }

            if(Utilities::isDefined($data["service_session_id"] ?? null)) {

                ServiceOperationService::attachSale(
                    (int) $companyId,
                    (int) $userId,
                    (int) $data["service_session_id"],
                    (int) $saleHeader->id
                );

            }

        });

        return $saleHeader;

    }

    /**
     * Cancel a sale
     *
     * @param SaleHeader $saleHeader Sale header instance
     * @param int $companyId Company that owns the sale
     * @param int $userId User ID canceling the sale
     * @return SaleHeader Updated sale header instance
     * @throws Exception
     */
    public static function cancel(SaleHeader $saleHeader, int $companyId, int $userId): SaleHeader {

        $stockRestored = false;
        $restoreStockPolicyEnabled = false;

        DB::transaction(function() use(
            $saleHeader,
            $companyId,
            $userId,
            &$stockRestored,
            &$restoreStockPolicyEnabled
        ) {

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

            $allPositions = $saleHeader->allPositions;
            $productPositions = $allPositions->where("type", "product");
            $fallbackWarehouse = $restoreStockPolicyEnabled && $productPositions->isNotEmpty()
                ? $saleHeader->serie?->branch?->warehouses?->first()
                : null;

            $saleMovements = $restoreStockPolicyEnabled && $allPositions->isNotEmpty()
                ? InventoryMovement::query()
                    ->where("company_id", $companyId)
                    ->whereIn("origin_type", [
                        InventoryMovementService::ORIGIN_SALE,
                        InventoryMovementService::ORIGIN_RECIPE_SALE
                    ])
                    ->whereIn("origin_id", $allPositions->pluck("id"))
                    ->orderByDesc("id")
                    ->get([
                        "id",
                        "warehouse_id",
                        "item_id",
                        "origin_id",
                        "origin_type",
                        "quantity_change",
                        "unit_cost"
                    ])
                    ->groupBy("origin_id")
                : collect();

            $inventoryPositions = $allPositions->filter(fn($position) =>
                $position->type === "product" || $saleMovements->has($position->id)
            );

            if($restoreStockPolicyEnabled
                && $productPositions->isNotEmpty()
                && !$fallbackWarehouse
                && $productPositions->contains(fn($position) => !$saleMovements->has($position->id))) {

                throw new Exception("No se encontró el almacén asociado a la venta.");

            }

            if($restoreStockPolicyEnabled && $inventoryPositions->isNotEmpty()) {

                foreach($inventoryPositions as $saleBody) {

                    $positionMovements = $saleMovements->get($saleBody->id, collect());

                    if($positionMovements->isEmpty()) {

                        if($saleBody->type !== "product") {

                            continue;

                        }

                        $positionMovements = collect([(object) [
                            "warehouse_id" => $fallbackWarehouse?->id,
                            "item_id" => $saleBody->item_id,
                            "quantity_change" => -(float) $saleBody->quantity,
                            "unit_cost" => 0,
                            "origin_type" => InventoryMovementService::ORIGIN_SALE
                        ]]);

                    }

                    foreach($positionMovements as $movement) {

                        if((int) $movement->warehouse_id <= 0) {

                            throw new Exception("No se encontró el almacén original de uno de los productos.");

                        }

                        InventoryMovementService::apply([
                            "company_id"    => $companyId,
                            "warehouse_id"  => (int) $movement->warehouse_id,
                            "item_id"       => (int) $movement->item_id,
                            "user_id"       => $userId,
                            "movement_type" => InventoryMovementService::TYPE_ENTRY,
                            "origin_type"   => InventoryMovementService::ORIGIN_SALE_CANCELLATION,
                            "origin_id"     => (int) $saleBody->id,
                            "quantity"      => abs((float) $movement->quantity_change),
                            "unit_cost"     => (float) $movement->unit_cost,
                            "reason"        => "Devolución automática por anulación de venta.",
                            "metadata"      => [
                                "sale_header_id" => (int) $saleHeader->id,
                                "automatic_return" => true,
                                "original_origin_type" => $movement->origin_type
                            ]
                        ]);

                    }

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

            $correlativeSource = (string) (
                DB::table("series_correlative_movements")
                    ->where("sale_header_id", $saleHeader->id)
                    ->where("action", self::CORRELATIVE_ISSUED)
                    ->value("source")
                ?? "sale"
            );

            self::recordCorrelativeMovement(
                $saleHeader,
                self::CORRELATIVE_CANCELED,
                $companyId,
                (int) $userId,
                $correlativeSource
            );

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
    public static function findById(int $companyId, int $id): ?SaleHeader {

        return SaleHeader::query()
            ->where("company_id", $companyId)
            ->find($id);

    }

    /**
     * Get paginated list of sales
     *
     * @param int $companyId Company ID
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(
        int $companyId,
        array $filters = [],
        int $perPage = 15,
        ?int $userId = null
    ) {

        $branchQuery = \App\Models\System\Organizations\Branch::where("company_id", $companyId)
            ->with(["series"]);
        $branchIds = $userId === null
            ? null
            : \App\Services\System\Base\CompanyReferenceDataService::for($companyId, $userId)->allowedBranchIds();

        if($branchIds !== null) {
            $branchQuery->whereIn("id", $branchIds);
        }

        $branches = $branchQuery->get();

        $serieIds = $branches->pluck("series.*.id")->flatten();

        $query = SaleHeader::whereIn("serie_id", $serieIds)
                           ->with(["serie.documentType", "serie.branch", "holder", "currency", "warehouse", "taxes", "payments"]);

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

        if(Utilities::isDefined($filters["start_date"])) {

            $query->whereDate("issue_date", ">=", $filters["start_date"]);

        }

        if(Utilities::isDefined($filters["end_date"])) {

            $query->whereDate("issue_date", "<=", $filters["end_date"]);

        }

        if(Utilities::isDefined($filters["branch_id"])) {

            $query->whereHas("serie", fn($serie) => $serie->where("branch_id", $filters["branch_id"]));

        }

        if(Utilities::isDefined($filters["holder_id"])) {

            $query->where("holder_id", $filters["holder_id"]);

        }

        if(Utilities::isDefined($filters["status"])) {

            $query->where("status", $filters["status"]);

        }

    }

}

