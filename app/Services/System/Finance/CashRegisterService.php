<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

use App\Models\System\Finance\{CashMovement, CashRegister, CashSession, CashSessionPayment, PaymentMethod};

final class CashRegisterService {

    public function listRegisters(int $companyId) {

        return CashRegister::query()
                           ->with(["branch", "openSession.paymentSummary.paymentMethod"])
                           ->where("company_id", $companyId)
                           ->orderBy("name")
                           ->get()
                           ->map(fn(CashRegister $register) => $this->formatRegister($register));

    }

    public function listSessions(int $companyId, array $filters, int $perPage): LengthAwarePaginator {

        return $this->sessionsQuery($companyId, $filters)
                    ->latest("opened_at")
                    ->paginate($perPage);

    }

    public function listMovements(int $companyId, array $filters, int $perPage): LengthAwarePaginator {

        return CashMovement::query()
                           ->with(["branch", "cashSession.register", "paymentMethod", "user"])
                           ->where("company_id", $companyId)
                           ->when($filters["branch_id"] ?? null, fn($query, $branchId) => $query->where("branch_id", $branchId))
                           ->when($filters["cash_session_id"] ?? null, fn($query, $sessionId) => $query->where("cash_session_id", $sessionId))
                           ->when($filters["payment_method_id"] ?? null, fn($query, $paymentMethodId) => $query->where("payment_method_id", $paymentMethodId))
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
                           ->latest("occurred_at")
                           ->paginate($perPage);

    }

    public function summary(int $companyId, array $filters): array {

        $sessions = $this->sessionsQuery($companyId, $filters)->get();
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

        $expected = round((float) $sessions->sum("expected_amount"), 2);
        $counted = round((float) $sessions->sum("counted_amount"), 2);

        return [
            "sessions" => $sessions,
            "payments" => $payments,
            "totals" => [
                "opening" => round((float) $sessions->sum("opening_amount"), 2),
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
                    "cash_session_id" => $session->id,
                    "payment_method_id" => $payment["payment_method_id"],
                    "payment_method_name" => $paymentMethod?->name ?? "Efectivo / apertura",
                    "expected_amount" => $expectedByMethod,
                    "counted_amount" => $payment["counted_amount"],
                    "difference_amount" => round($payment["counted_amount"] - $expectedByMethod, 2),
                    "created_by" => $userId
                ]);

            }

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

            return $session->load(["register", "branch", "closedBy", "paymentSummary.paymentMethod"]);

        });

    }

    private function sessionsQuery(int $companyId, array $filters): Builder {

        return CashSession::query()
                          ->with(["register", "branch", "openedBy", "closedBy", "paymentSummary.paymentMethod"])
                          ->where("company_id", $companyId)
                          ->when($filters["branch_id"] ?? null, fn($query, $branchId) => $query->where("branch_id", $branchId))
                          ->when($filters["cash_register_id"] ?? null, fn($query, $registerId) => $query->where("cash_register_id", $registerId))
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

    }

    private function formatRegister(CashRegister $register): array {

        $openSession = $register->openSession;

        return [
            "id" => $register->id,
            "code" => $register->code,
            "name" => $register->name,
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

    private function emptyTotals(): array {

        return [
            "opening" => 0,
            "expected" => 0,
            "counted" => 0,
            "difference" => 0
        ];

    }

}
