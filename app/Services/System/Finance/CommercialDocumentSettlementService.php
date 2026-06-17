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
        int $userId
    ): Collection {

        return self::activeTaxCatalog($companyId, $scope)
            ->map(fn(Tax $tax) => self::taxLine($tax, $baseAmount, $userId))
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

    private static function activeTaxCatalog(int $companyId, string $scope): Collection {

        return Tax::query()
            ->where("company_id", $companyId)
            ->whereIn("scope", [$scope, "both"])
            ->where("status", "active")
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

    private static function taxLine(Tax $tax, float $baseAmount, int $userId): array {

        $rate = round((float) $tax->rate, 4);
        $base = round($baseAmount, 2);
        $amount = round($base * ($rate / 100), 2);

        return [
            "tax_id" => $tax->id,
            "name" => (string) $tax->name,
            "rate" => $rate,
            "base_amount" => $base,
            "amount" => $amount,
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

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
