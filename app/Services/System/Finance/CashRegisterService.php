<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

use App\Models\System\Finance\{CashMovement, CashRegister, CashSession, CashSessionInventoryCount, CashSessionPayment, PaymentMethod};
use App\Models\System\Organizations\Branch;
use App\Models\System\Warehouses\WarehouseItem;
use App\Services\System\Base\CompanyReferenceDataService;
use App\Services\System\Sales\SaleConfigService;
use App\Services\System\Warehouses\Inventory\InventoryMovementService;

final class CashRegisterService {

    public function listRegisters(int $companyId, ?int $userId = null) {

        $query = CashRegister::query()
                             ->with(["branch", "openSession.paymentSummary.paymentMethod"])
                             ->where("company_id", $companyId);

        $cashRegisterIds = $userId !== null
            ? CompanyReferenceDataService::for($companyId, $userId)->allowedCashRegisterIds()
            : null;

        if($cashRegisterIds !== null) {

            $query->whereIn("id", $cashRegisterIds);

        }

        return $query->orderBy("name")
                     ->get()
                     ->map(fn(CashRegister $register) => $this->formatRegister($register));

    }

    public function createRegister(int $companyId, int $userId, array $data): CashRegister {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $branch = Branch::query()
                            ->where("company_id", $companyId)
                            ->where("status", "active")
                            ->find((int) $data["branch_id"]);

            if(!$branch) {

                throw new RuntimeException("Seleccione una sucursal activa para registrar la caja.");

            }

            $allowedBranchIds = CompanyReferenceDataService::for($companyId, $userId)->allowedBranchIds();
            if($allowedBranchIds !== null && !in_array((int) $branch->id, $allowedBranchIds, true)) {

                throw new RuntimeException("No tienes acceso a la sucursal seleccionada.");

            }

            if((bool) ($data["is_main"] ?? false)) {

                CashRegister::query()
                            ->where("company_id", $companyId)
                            ->where("branch_id", $branch->id)
                            ->update(["is_main" => false]);

            }

            $register = CashRegister::create([
                "company_id" => $companyId,
                "branch_id" => $branch->id,
                "code" => $data["code"] ?? $this->generateRegisterCode($companyId),
                "name" => $data["name"],
                "is_main" => (bool) ($data["is_main"] ?? false),
                "status" => $data["status"] ?? "active",
                "created_by" => $userId
            ]);

            $this->clearOperationalCaches($companyId);

            return $register->load("branch");

        });

    }

    public function listSessions(int $companyId, array $filters, int $perPage, ?int $userId = null): LengthAwarePaginator {

        return $this->sessionsQuery($companyId, $filters, $userId)
                    ->latest("opened_at")
                    ->paginate($perPage);

    }

    public function listMovements(int $companyId, array $filters, int $perPage, ?int $userId = null): LengthAwarePaginator {

        $query = CashMovement::query()
                           ->with(["branch", "cashSession.register", "paymentMethod", "user"])
                           ->where("company_id", $companyId)
                           ->when($filters["branch_id"] ?? null, fn($query, $branchId) => $query->where("branch_id", $branchId))
                           ->when($filters["cash_session_id"] ?? null, fn($query, $sessionId) => $query->where("cash_session_id", $sessionId))
                           ->when($filters["payment_method_id"] ?? null, fn($query, $paymentMethodId) => $query->where("payment_method_id", $paymentMethodId))
                           ->when($filters["user_id"] ?? null, fn($query, $responsibleId) => $query->where("user_id", $responsibleId))
                           ->when($filters["movement_type"] ?? null, fn($query, $type) => $query->where("movement_type", $type))
                           ->when($filters["cash_register_id"] ?? null, function($query, $registerId) {

                               $query->whereHas("cashSession", fn($sessionQuery) => $sessionQuery->where("cash_register_id", $registerId));

                           })
                           ->when($filters["search"] ?? null, function($query, $search) {

                               $query->where(function($subQuery) use($search) {

                                   $subQuery->where("reference", "like", "%{$search}%")
                                            ->orWhere("note", "like", "%{$search}%")
                                            ->orWhereHas("cashSession.register", function($registerQuery) use($search) {

                                                $registerQuery->where("name", "like", "%{$search}%")
                                                              ->orWhere("code", "like", "%{$search}%");

                                            })
                                            ->orWhereHas("user", fn($userQuery) => $userQuery->where("name", "like", "%{$search}%"));

                               });

                           })
                           ->when($filters["date_from"] ?? null, fn($query, $date) => $query->whereDate("occurred_at", ">=", $date))
                           ->when($filters["date_to"] ?? null, fn($query, $date) => $query->whereDate("occurred_at", "<=", $date))
                           ->where("status", "active")
                           ->latest("occurred_at");

        $cashRegisterIds = $this->allowedCashRegisterIds($companyId, $userId);
        if($cashRegisterIds !== null) {
            $query->whereHas("cashSession", fn($sessionQuery) => $sessionQuery->whereIn("cash_register_id", $cashRegisterIds));
        }

        return $query->paginate($perPage);

    }

    public function summary(int $companyId, array $filters, ?int $userId = null): array {

        $sessions = $this->sessionsQuery($companyId, $filters, $userId)->get();
        $sessionIds = $sessions->pluck("id")->all();

        if(empty($sessionIds)) {

            return [
                "sessions" => [],
                "payments" => [],
                "totals" => $this->emptyTotals()
            ];

        }

        $payments = CashMovement::query()
                               ->select([
                                   "payment_method_id",
                                   DB::raw("SUM(amount) as amount")
                               ])
                               ->with("paymentMethod")
                               ->where("company_id", $companyId)
                               ->whereIn("cash_session_id", $sessionIds)
                               ->where("status", "active")
                               ->when($filters["payment_method_id"] ?? null, fn($query, $paymentMethodId) => $query->where("payment_method_id", $paymentMethodId))
                               ->groupBy("payment_method_id")
                               ->get()
                               ->map(function($row) {

                                   return [
                                       "payment_method_id" => $row->payment_method_id,
                                       "payment_method" => $row->paymentMethod,
                                       "amount" => round((float) $row->amount, 2)
                                   ];

                               })
                               ->values();

        $paymentMethodId = $filters["payment_method_id"] ?? null;
        $expected = $paymentMethodId
            ? round((float) CashMovement::query()
                ->where("company_id", $companyId)
                ->whereIn("cash_session_id", $sessionIds)
                ->where("payment_method_id", $paymentMethodId)
                ->where("status", "active")
                ->sum("amount"), 2)
            : round((float) $sessions->sum("expected_amount"), 2);
        $counted = $paymentMethodId
            ? round((float) CashSessionPayment::query()
                ->where("company_id", $companyId)
                ->whereIn("cash_session_id", $sessionIds)
                ->where("payment_method_id", $paymentMethodId)
                ->sum("counted_amount"), 2)
            : round((float) $sessions->sum("counted_amount"), 2);

        return [
            "sessions" => $sessions,
            "payments" => $payments,
            "totals" => [
                "opening" => $paymentMethodId ? 0 : round((float) $sessions->sum("opening_amount"), 2),
                "expected" => $expected,
                "counted" => $counted,
                "difference" => round($counted - $expected, 2)
            ]
        ];

    }

    public function openSession(int $companyId, int $userId, array $data): CashSession {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $register = CashRegister::query()
                                    ->with("branch")
                                    ->where("company_id", $companyId)
                                    ->where("status", "active")
                                    ->findOrFail((int) $data["cash_register_id"]);

            $this->assertRegisterAccess($companyId, $userId, (int) $register->id);

            $hasOpenSession = CashSession::query()
                                         ->where("company_id", $companyId)
                                         ->where("cash_register_id", $register->id)
                                         ->where("status", "open")
                                         ->exists();

            if($hasOpenSession) {

                throw new RuntimeException("Esta caja ya tiene una apertura activa.");

            }

            $openingAmount = round((float) ($data["opening_amount"] ?? 0), 2);

            $session = CashSession::create([
                "company_id" => $companyId,
                "branch_id" => $register->branch_id,
                "cash_register_id" => $register->id,
                "opened_by" => $userId,
                "opened_at" => Carbon::now(),
                "opening_amount" => $openingAmount,
                "expected_amount" => $openingAmount,
                "counted_amount" => 0,
                "difference_amount" => 0,
                "observation" => $data["observation"] ?? null,
                "status" => "open",
                "created_by" => $userId
            ]);

            CashMovement::create([
                "company_id" => $companyId,
                "branch_id" => $register->branch_id,
                "cash_session_id" => $session->id,
                "user_id" => $userId,
                "movement_type" => "opening",
                "origin_type" => "cash_session",
                "origin_id" => $session->id,
                "amount" => $openingAmount,
                "reference" => "Apertura de caja",
                "note" => $data["observation"] ?? null,
                "occurred_at" => Carbon::now(),
                "status" => "active",
                "created_by" => $userId
            ]);

            $this->clearOperationalCaches($companyId);

            return $session->load(["register", "branch", "openedBy"]);

        });

    }

    public function closeSession(int $companyId, int $userId, array $data): CashSession {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $session = CashSession::query()
                                  ->with(["register", "branch"])
                                  ->where("company_id", $companyId)
                                  ->where("status", "open")
                                  ->findOrFail((int) $data["cash_session_id"]);

            $this->assertRegisterAccess($companyId, $userId, (int) $session->cash_register_id);

            if($session->register?->is_main) {

                $hasOpenSecondarySessions = CashSession::query()
                                                       ->where("company_id", $companyId)
                                                       ->where("branch_id", $session->branch_id)
                                                       ->where("status", "open")
                                                       ->where("id", "!=", $session->id)
                                                       ->whereHas("register", fn($query) => $query->where("is_main", false))
                                                       ->exists();

                if($hasOpenSecondarySessions) {

                    throw new RuntimeException("Primero cierra las cajas secundarias de la sucursal antes de cerrar la caja principal.");

                }

            }

            $expectedAmount = round((float) CashMovement::query()
                                                        ->where("company_id", $companyId)
                                                        ->where("cash_session_id", $session->id)
                                                        ->where("status", "active")
                                                        ->sum("amount"), 2);

            $countedPayments = collect($data["payments"] ?? [])
                               ->map(function($payment) {

                                   return [
                                       "payment_method_id" => $payment["payment_method_id"] ?? null,
                                       "counted_amount" => round((float) ($payment["counted_amount"] ?? 0), 2)
                                   ];

                               });

            $countedAmount = $countedPayments->isNotEmpty()
                ? round((float) $countedPayments->sum("counted_amount"), 2)
                : round((float) ($data["counted_amount"] ?? 0), 2);

            $session->update([
                "closed_by" => $userId,
                "closed_at" => Carbon::now(),
                "expected_amount" => $expectedAmount,
                "counted_amount" => $countedAmount,
                "difference_amount" => round($countedAmount - $expectedAmount, 2),
                "observation" => $data["observation"] ?? $session->observation,
                "status" => "closed",
                "updated_by" => $userId
            ]);

            CashSessionPayment::query()->where("cash_session_id", $session->id)->delete();

            foreach($countedPayments as $payment) {

                $expectedByMethod = $this->expectedByPaymentMethod($companyId, $session->id, $payment["payment_method_id"]);
                $paymentMethod = $payment["payment_method_id"]
                    ? PaymentMethod::query()->where("company_id", $companyId)->find($payment["payment_method_id"])
                    : null;

                CashSessionPayment::create([
                    "company_id" => $companyId,
                    "cash_session_id" => $session->id,
                    "payment_method_id" => $payment["payment_method_id"],
                    "payment_method_name" => $paymentMethod?->name ?? "Efectivo / apertura",
                    "expected_amount" => $expectedByMethod,
                    "counted_amount" => $payment["counted_amount"],
                    "difference_amount" => round($payment["counted_amount"] - $expectedByMethod, 2),
                    "created_by" => $userId
                ]);

            }

            $this->syncInventoryCounts(
                $companyId,
                $userId,
                $session,
                is_array($data["inventory_counts"] ?? null) ? $data["inventory_counts"] : []
            );

            CashMovement::create([
                "company_id" => $companyId,
                "branch_id" => $session->branch_id,
                "cash_session_id" => $session->id,
                "user_id" => $userId,
                "movement_type" => "closing",
                "origin_type" => "cash_session",
                "origin_id" => $session->id,
                "amount" => 0,
                "reference" => "Cierre de caja",
                "note" => $data["observation"] ?? null,
                "occurred_at" => Carbon::now(),
                "status" => "active",
                "created_by" => $userId
            ]);

            $this->clearOperationalCaches($companyId);

            return $session->load(["register", "branch", "closedBy", "paymentSummary.paymentMethod", "inventoryCounts.item", "inventoryCounts.warehouse"]);

        });

    }

    public function registerMovement(int $companyId, int $userId, array $data): CashMovement {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $session = CashSession::query()
                                  ->with("register")
                                  ->where("company_id", $companyId)
                                  ->where("status", "open")
                                  ->findOrFail((int) $data["cash_session_id"]);

            $this->assertRegisterAccess($companyId, $userId, (int) $session->cash_register_id);

            $movementType = (string) $data["movement_type"];
            $amount = round((float) $data["amount"], 2);

            if(in_array($movementType, ["expense"], true)) {

                $amount = abs($amount) * -1;

            }else {

                $amount = abs($amount);

            }

            return CashMovement::create([
                "company_id" => $companyId,
                "branch_id" => $session->branch_id,
                "cash_session_id" => $session->id,
                "payment_method_id" => $data["payment_method_id"] ?? null,
                "user_id" => $userId,
                "movement_type" => $movementType,
                "origin_type" => "cash_manual",
                "origin_id" => null,
                "amount" => $amount,
                "reference" => $data["reference"] ?? $this->manualMovementLabel($movementType),
                "note" => $data["note"] ?? null,
                "occurred_at" => Carbon::now(),
                "status" => "active",
                "created_by" => $userId
            ])->load(["branch", "cashSession.register", "paymentMethod", "user"]);

        });

    }

    public function movementsForExport(int $companyId, array $filters, ?int $userId = null) {

        $query = CashMovement::query()
                           ->with(["branch", "cashSession.register", "paymentMethod", "user"])
                           ->where("company_id", $companyId)
                           ->when($filters["branch_id"] ?? null, fn($query, $branchId) => $query->where("branch_id", $branchId))
                           ->when($filters["cash_register_id"] ?? null, function($query, $registerId) {

                               $query->whereHas("cashSession", fn($sessionQuery) => $sessionQuery->where("cash_register_id", $registerId));

                           })
                           ->when($filters["payment_method_id"] ?? null, fn($query, $paymentMethodId) => $query->where("payment_method_id", $paymentMethodId))
                           ->when($filters["user_id"] ?? null, fn($query, $responsibleId) => $query->where("user_id", $responsibleId))
                           ->when($filters["date_from"] ?? null, fn($query, $date) => $query->whereDate("occurred_at", ">=", $date))
                           ->when($filters["date_to"] ?? null, fn($query, $date) => $query->whereDate("occurred_at", "<=", $date))
                           ->where("status", "active")
                           ->latest("occurred_at");

        $cashRegisterIds = $this->allowedCashRegisterIds($companyId, $userId);
        if($cashRegisterIds !== null) {
            $query->whereHas("cashSession", fn($sessionQuery) => $sessionQuery->whereIn("cash_register_id", $cashRegisterIds));
        }

        return $query->get();

    }

    private function sessionsQuery(int $companyId, array $filters, ?int $userId = null): Builder {

        $query = CashSession::query()
                          ->with(["register", "branch", "openedBy", "closedBy", "paymentSummary.paymentMethod"])
                          ->where("company_id", $companyId)
                          ->when($filters["branch_id"] ?? null, fn($query, $branchId) => $query->where("branch_id", $branchId))
                          ->when($filters["cash_register_id"] ?? null, fn($query, $registerId) => $query->where("cash_register_id", $registerId))
                          ->when($filters["user_id"] ?? null, function($query, $responsibleId) {

                              $query->where(function($userQuery) use($responsibleId) {
                                  $userQuery->where("opened_by", $responsibleId)
                                      ->orWhere("closed_by", $responsibleId);
                              });

                          })
                          ->when($filters["payment_method_id"] ?? null, function($query, $paymentMethodId) {

                              $query->where(function($paymentQuery) use($paymentMethodId) {
                                  $paymentQuery->whereHas("paymentSummary", fn($summaryQuery) => $summaryQuery
                                      ->where("payment_method_id", $paymentMethodId))
                                      ->orWhereHas("movements", fn($movementQuery) => $movementQuery
                                          ->where("payment_method_id", $paymentMethodId)
                                          ->where("status", "active"));
                              });

                          })
                          ->when($filters["status"] ?? null, fn($query, $status) => $query->where("status", $status))
                          ->when($filters["search"] ?? null, function($query, $search) {

                              $query->where(function($subQuery) use($search) {

                                  $subQuery->where("observation", "like", "%{$search}%")
                                           ->orWhereHas("register", function($registerQuery) use($search) {

                                               $registerQuery->where("name", "like", "%{$search}%")
                                                             ->orWhere("code", "like", "%{$search}%");

                                           })
                                           ->orWhereHas("openedBy", fn($userQuery) => $userQuery->where("name", "like", "%{$search}%"))
                                           ->orWhereHas("closedBy", fn($userQuery) => $userQuery->where("name", "like", "%{$search}%"));

                              });

                          })
                          ->when($filters["date_from"] ?? null, fn($query, $date) => $query->whereDate("opened_at", ">=", $date))
                          ->when($filters["date_to"] ?? null, fn($query, $date) => $query->whereDate("opened_at", "<=", $date));

        $cashRegisterIds = $this->allowedCashRegisterIds($companyId, $userId);
        if($cashRegisterIds !== null) {
            $query->whereIn("cash_register_id", $cashRegisterIds);
        }

        return $query;

    }

    private function allowedCashRegisterIds(int $companyId, ?int $userId): ?array {

        return $userId === null
            ? null
            : CompanyReferenceDataService::for($companyId, $userId)->allowedCashRegisterIds();

    }

    private function formatRegister(CashRegister $register): array {

        $openSession = $register->openSession;

        return [
            "id" => $register->id,
            "code" => $register->code,
            "name" => $register->name,
            "is_main" => (bool) $register->is_main,
            "status" => $register->status,
            "branch" => $register->branch,
            "open_session" => $openSession,
            "is_open" => $openSession !== null,
            "current_amount" => $openSession ? round((float) $openSession->expected_amount, 2) : 0
        ];

    }

    private function expectedByPaymentMethod(int $companyId, int $sessionId, ?int $paymentMethodId): float {

        return round((float) CashMovement::query()
                                         ->where("company_id", $companyId)
                                         ->where("cash_session_id", $sessionId)
                                         ->where("status", "active")
                                         ->when($paymentMethodId === null, fn($query) => $query->whereNull("payment_method_id"))
                                         ->when($paymentMethodId !== null, fn($query) => $query->where("payment_method_id", $paymentMethodId))
                                         ->sum("amount"), 2);

    }

    private function syncInventoryCounts(int $companyId, int $userId, CashSession $session, array $counts): void {

        if(!$session->register?->is_main || empty($counts)) {

            return;

        }

        CashSessionInventoryCount::query()
            ->where("cash_session_id", $session->id)
            ->delete();

        foreach($counts as $count) {

            $warehouseId = (int) ($count["warehouse_id"] ?? 0);
            $itemId = (int) ($count["item_id"] ?? 0);

            if($warehouseId <= 0 || $itemId <= 0) {

                continue;

            }

            $warehouseItem = WarehouseItem::query()
                ->whereHas("warehouse", function($query) use($companyId, $session) {

                    $query->where("company_id", $companyId)
                          ->where("branch_id", $session->branch_id);

                })
                ->whereHas("item", fn($query) => $query->where("company_id", $companyId))
                ->where("warehouse_id", $warehouseId)
                ->where("item_id", $itemId)
                ->first();

            $systemQuantity = round((float) ($warehouseItem?->quantity ?? 0), 2);
            $countedQuantity = round((float) ($count["counted_quantity"] ?? $systemQuantity), 2);
            $difference = round($countedQuantity - $systemQuantity, 2);
            $movement = null;

            if(abs($difference) >= 0.00001) {

                $movement = InventoryMovementService::apply([
                    "company_id" => $companyId,
                    "warehouse_id" => $warehouseId,
                    "item_id" => $itemId,
                    "user_id" => $userId,
                    "movement_type" => InventoryMovementService::TYPE_CORRECTION,
                    "origin_type" => InventoryMovementService::ORIGIN_PHYSICAL_COUNT,
                    "origin_id" => $session->id,
                    "resulting_balance" => $countedQuantity,
                    "reason" => "Ajuste por conteo físico en cierre de caja principal.",
                    "metadata" => [
                        "cash_session_id" => $session->id,
                        "cash_register_id" => $session->cash_register_id,
                        "observation" => $count["observation"] ?? null
                    ],
                    "allow_negative" => false
                ]);

            }

            CashSessionInventoryCount::create([
                "company_id" => $companyId,
                "branch_id" => $session->branch_id,
                "cash_session_id" => $session->id,
                "warehouse_id" => $warehouseId,
                "item_id" => $itemId,
                "inventory_movement_id" => $movement?->id,
                "system_quantity" => $systemQuantity,
                "counted_quantity" => $countedQuantity,
                "difference_quantity" => $difference,
                "observation" => $count["observation"] ?? null,
                "status" => $movement ? "adjusted" : "ignored",
                "created_by" => $userId
            ]);

        }

    }

    private function emptyTotals(): array {

        return [
            "opening" => 0,
            "expected" => 0,
            "counted" => 0,
            "difference" => 0
        ];

    }

    private function manualMovementLabel(string $movementType): string {

        return match($movementType) {
            "income" => "Ingreso manual de caja",
            "expense" => "Salida manual de caja",
            "adjustment" => "Ajuste manual de caja",
            default => "Movimiento manual de caja"
        };

    }

    private function generateRegisterCode(int $companyId): string {

        do {

            $code = "CAJ-" . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        }while(CashRegister::query()->where("company_id", $companyId)->where("code", $code)->exists());

        return $code;

    }

    private function assertRegisterAccess(int $companyId, int $userId, int $cashRegisterId): void {

        $allowedIds = $this->allowedCashRegisterIds($companyId, $userId);

        if($allowedIds !== null && !in_array($cashRegisterId, $allowedIds, true)) {

            throw new RuntimeException("No tienes acceso a la caja seleccionada.");

        }

    }

    private function clearOperationalCaches(int $companyId): void {

        CashRegisterConfigService::clearAllCache($companyId);
        SaleConfigService::clearAllCache($companyId);

    }

}
