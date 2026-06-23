<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use DomainException;
use Illuminate\Support\Collection;

use App\Models\System\Finance\{PaymentMethod, Tax};

final class CommercialDocumentSettlementService {

    public static function taxes(
        int $companyId,
        string $scope,
        float $baseAmount,
        int $userId,
        array $selectedTaxIds = [],
        array $selectedTaxQuantities = []
    ): Collection {

        return self::activeTaxCatalog($companyId, $scope, $selectedTaxIds)
            ->map(fn(Tax $tax) => self::taxLine($tax, $baseAmount, $userId, self::taxQuantity($tax, $selectedTaxQuantities)))
            ->values();

    }

    public static function saleTaxes(
        int $companyId,
        array $details,
        int $userId,
        array $selectedTaxIds = [],
        array $selectedTaxQuantities = []
    ): Collection {

        return self::activeTaxCatalog($companyId, "sale", $selectedTaxIds)
            ->map(fn(Tax $tax) => self::saleTaxLine($tax, $details, $userId, self::taxQuantity($tax, $selectedTaxQuantities)))
            ->values();

    }

    public static function payments(
        int $companyId,
        string $scope,
        float $total,
        array $selectedPayments,
        int $userId
    ): Collection {

        if(empty($selectedPayments)) {

            $defaultMethod = PaymentMethod::query()
                ->where("company_id", $companyId)
                ->whereIn("scope", [$scope, "both"])
                ->where("status", "active")
                ->orderByDesc("is_default")
                ->orderBy("name")
                ->first();

            if(!$defaultMethod) return collect();

            $selectedPayments = [[
                "payment_method_id" => $defaultMethod->id,
                "amount" => $total,
                "reference" => null,
                "note" => null
            ]];

        }

        $methods = self::paymentCatalog($companyId, $scope, $selectedPayments)
            ->keyBy("id");

        $payments = collect($selectedPayments)
            ->map(fn($paymentData) => self::paymentLine($methods, $paymentData, $userId))
            ->filter()
            ->values();

        $paid = round((float) $payments->sum("amount"), 2);

        if(abs($paid - round($total, 2)) > 0.01) {

            throw new DomainException("El total de los métodos de pago debe coincidir con el total del documento.");

        }

        return $payments;

    }

    private static function activeTaxCatalog(int $companyId, string $scope, array $selectedTaxIds = []): Collection {

        $selectedTaxIds = collect($selectedTaxIds)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return Tax::query()
            ->where("company_id", $companyId)
            ->whereIn("scope", [$scope, "both"])
            ->where("status", "active")
            ->where(function($query) use($selectedTaxIds) {

                $query->where("is_required", true);

                if(!empty($selectedTaxIds)) {

                    $query->orWhereIn("id", $selectedTaxIds);

                }

            })
            ->orderByDesc("is_default")
            ->orderBy("name")
            ->get();

    }

    private static function paymentCatalog(int $companyId, string $scope, array $selectedPayments): Collection {

        $ids = collect($selectedPayments)
            ->pluck("payment_method_id")
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if($ids->isEmpty()) return collect();

        $methods = PaymentMethod::query()
            ->where("company_id", $companyId)
            ->whereIn("scope", [$scope, "both"])
            ->where("status", "active")
            ->whereIn("id", $ids)
            ->get();

        if($methods->count() !== $ids->count()) {

            throw new DomainException("Uno de los métodos de pago no está disponible para este documento.");

        }

        return $methods;

    }

    private static function taxLine(Tax $tax, float $baseAmount, int $userId, int $quantity = 1): array {

        $rate = round((float) $tax->rate, 4);
        $base = round($baseAmount, 2);
        $calculationType = in_array($tax->calculation_type, ["percentage", "fixed"], true)
            ? $tax->calculation_type
            : "percentage";
        $operationType = in_array($tax->operation_type, ["addition", "subtraction"], true)
            ? $tax->operation_type
            : "addition";
        $amount = self::taxAmount($base, $rate, $calculationType, $operationType, $quantity);

        return [
            "company_id" => $tax->company_id,
            "tax_id" => $tax->id,
            "name" => (string) $tax->name,
            "description" => $tax->description,
            "rate" => $rate,
            "calculation_type" => $calculationType,
            "operation_type" => $operationType,
            "is_required" => (bool) $tax->is_required,
            "quantity" => $calculationType === "fixed" ? $quantity : 1,
            "base_amount" => $base,
            "amount" => $amount,
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

    }

    private static function saleTaxLine(Tax $tax, array $details, int $userId, int $quantity = 1): array {

        $rate = round((float) $tax->rate, 4);
        $calculationType = in_array($tax->calculation_type, ["percentage", "fixed"], true)
            ? $tax->calculation_type
            : "percentage";
        $operationType = in_array($tax->operation_type, ["addition", "subtraction"], true)
            ? $tax->operation_type
            : "addition";
        $base = 0.0;
        $amount = 0.0;
        $totalImpact = 0.0;

        if($calculationType === "fixed") {

            $amount = self::taxAmount(0, $rate, $calculationType, $operationType, $quantity);
            $totalImpact = $amount;

        }else {

            foreach($details as $detail) {

                $lineTotal = round((float) ($detail["quantity"] ?? 0) * (float) ($detail["price"] ?? 0), 2);
                if($lineTotal <= 0) continue;

                $priceIncludesTax = filter_var($detail["price_includes_tax"] ?? true, FILTER_VALIDATE_BOOL);
                $taxIsIncluded = $priceIncludesTax && $operationType === "addition" && $rate > 0;

                if($taxIsIncluded) {

                    $lineBase = round($lineTotal / (1 + ($rate / 100)), 2);
                    $lineAmount = round($lineTotal - $lineBase, 2);
                    $base += $lineBase;
                    $amount += $lineAmount;
                    continue;

                }

                $lineAmount = self::taxAmount($lineTotal, $rate, $calculationType, $operationType);
                $base += $lineTotal;
                $amount += $lineAmount;
                $totalImpact += $lineAmount;

            }

        }

        return [
            "company_id" => $tax->company_id,
            "tax_id" => $tax->id,
            "name" => (string) $tax->name,
            "description" => $tax->description,
            "rate" => $rate,
            "calculation_type" => $calculationType,
            "operation_type" => $operationType,
            "is_required" => (bool) $tax->is_required,
            "quantity" => $calculationType === "fixed" ? $quantity : 1,
            "base_amount" => round($base, 2),
            "amount" => round($amount, 2),
            "_total_impact" => round($totalImpact, 2),
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

    }

    private static function taxAmount(float $base, float $rate, string $calculationType, string $operationType, int $quantity = 1): float {

        $quantity = max(1, $quantity);

        $amount = match($calculationType) {
            "fixed" => round($rate * $quantity, 2),
            default => round($base * ($rate / 100), 2)
        };

        if($operationType === "subtraction") {

            $amount *= -1;

        }

        return round($amount, 2);

    }

    private static function taxQuantity(Tax $tax, array $selectedTaxQuantities): int {

        if($tax->calculation_type !== "fixed") return 1;

        $minimum = max(0, (int) ($tax->min_apply_quantity ?? 0));
        $maximum = $tax->max_apply_quantity !== null ? max($minimum, (int) $tax->max_apply_quantity) : null;
        $quantity = max(1, $minimum, (int) ($selectedTaxQuantities[$tax->id] ?? 1));

        return $maximum !== null ? min($quantity, $maximum) : $quantity;

    }

    private static function paymentLine(Collection $methods, array $paymentData, int $userId): ?array {

        $methodId = (int) ($paymentData["payment_method_id"] ?? 0);
        if($methodId <= 0) return null;

        $method = $methods->get($methodId);
        $amount = round((float) ($paymentData["amount"] ?? 0), 2);

        if($amount <= 0) {

            throw new DomainException("Cada método de pago debe tener un importe mayor que cero.");

        }

        if($method?->requires_reference && trim((string) ($paymentData["reference"] ?? "")) === "") {

            throw new DomainException("El método de pago {$method->name} requiere referencia.");

        }

        return [
            "company_id" => $method?->company_id,
            "payment_method_id" => $method?->id,
            "name" => (string) ($paymentData["name"] ?? $method?->name),
            "amount" => $amount,
            "reference" => $paymentData["reference"] ?? null,
            "note" => $paymentData["note"] ?? null,
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

    }

}
