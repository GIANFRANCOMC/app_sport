<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Models\System\Purchases\{PurchaseAccountPayable, PurchaseHeader, PurchasePayableInstallment, PurchasePayablePayment};
use App\Models\System\Sales\{SaleAccountReceivable, SaleHeader, SaleReceivableInstallment, SaleReceivablePayment};

final class CommercialCreditAccountService {

    public const PAID_NOW = "paid_now";
    public const CASH_ON_DELIVERY = "cash_on_delivery";
    public const INSTALLMENTS = "installments";

    public static function normalizePaymentModality(?string $modality, string $default = self::PAID_NOW): string {

        return in_array($modality, [self::PAID_NOW, self::CASH_ON_DELIVERY, self::INSTALLMENTS], true)
            ? $modality
            : $default;

    }

    public static function paymentStatus(float $total, float $paid): string {

        $total = round($total, 4);
        $paid = round($paid, 4);
        $balance = round($total - $paid, 4);

        return match(true) {
            $paid <= 0 => "unpaid",
            $balance > 0 => "partial",
            $balance < 0 => "overpaid",
            default => "paid"
        };

    }

    public static function createReceivable(
        SaleHeader $sale,
        Collection $paymentLines,
        int $installmentCount,
        ?string $firstDueDate,
        int $userId
    ): ?SaleAccountReceivable {

        if($sale->payment_modality === self::PAID_NOW || round((float) $sale->balance_due, 4) <= 0) {

            return null;

        }

        $account = SaleAccountReceivable::create([
            "company_id" => $sale->company_id,
            "sale_header_id" => $sale->id,
            "customer_id" => $sale->holder_id,
            "currency_id" => $sale->currency_id,
            "issue_date" => $sale->issue_date,
            "due_date" => $firstDueDate,
            "payment_modality" => $sale->payment_modality,
            "original_amount" => round((float) $sale->total - (float) $sale->installment_extra_amount, 4),
            "extra_percentage" => $sale->installment_extra_percentage,
            "extra_amount" => $sale->installment_extra_amount,
            "total_amount" => $sale->total,
            "paid_amount" => $sale->paid_amount,
            "pending_amount" => $sale->balance_due,
            "status" => $sale->payment_status === "partial" ? "partial" : "pending",
            "created_at" => now(),
            "created_by" => $userId
        ]);

        self::createSaleInstallments($account, max(1, $installmentCount), $firstDueDate, $userId);
        self::createSalePaymentTrace($account, $paymentLines, $userId);

        return $account;

    }

    public static function createPayable(
        PurchaseHeader $purchase,
        Collection $paymentLines,
        int $installmentCount,
        ?string $firstDueDate,
        int $userId
    ): ?PurchaseAccountPayable {

        if($purchase->payment_modality === self::PAID_NOW || round((float) $purchase->balance_due, 4) <= 0) {

            return null;

        }

        $account = PurchaseAccountPayable::create([
            "company_id" => $purchase->company_id,
            "purchase_header_id" => $purchase->id,
            "supplier_id" => $purchase->supplier_id,
            "currency_id" => $purchase->currency_id,
            "issue_date" => $purchase->issue_date,
            "due_date" => $firstDueDate ?: $purchase->due_date,
            "payment_modality" => $purchase->payment_modality,
            "original_amount" => round((float) $purchase->total - (float) $purchase->installment_extra_amount, 4),
            "extra_percentage" => $purchase->installment_extra_percentage,
            "extra_amount" => $purchase->installment_extra_amount,
            "total_amount" => $purchase->total,
            "paid_amount" => $purchase->paid_amount,
            "pending_amount" => $purchase->balance_due,
            "status" => $purchase->payment_status === "partial" ? "partial" : "pending",
            "created_at" => now(),
            "created_by" => $userId
        ]);

        self::createPurchaseInstallments($account, max(1, $installmentCount), $firstDueDate ?: $purchase->due_date, $userId);
        self::createPurchasePaymentTrace($account, $paymentLines, $userId);

        return $account;

    }

    private static function createSaleInstallments(SaleAccountReceivable $account, int $count, ?string $firstDueDate, int $userId): void {

        $amount = round((float) $account->pending_amount / $count, 4);
        $rows = [];

        for($number = 1; $number <= $count; $number++) {
            $lineAmount = $number === $count
                ? round((float) $account->pending_amount - ($amount * ($count - 1)), 4)
                : $amount;

            $rows[] = [
                "company_id" => $account->company_id,
                "sale_account_receivable_id" => $account->id,
                "installment_number" => $number,
                "due_date" => $firstDueDate ? Carbon::parse($firstDueDate)->addMonthsNoOverflow($number - 1)->toDateString() : null,
                "amount" => $lineAmount,
                "paid_amount" => 0,
                "pending_amount" => $lineAmount,
                "status" => "pending",
                "created_at" => now(),
                "created_by" => $userId
            ];
        }

        SaleReceivableInstallment::insert($rows);

    }

    private static function createPurchaseInstallments(PurchaseAccountPayable $account, int $count, ?string $firstDueDate, int $userId): void {

        $amount = round((float) $account->pending_amount / $count, 4);
        $rows = [];

        for($number = 1; $number <= $count; $number++) {
            $lineAmount = $number === $count
                ? round((float) $account->pending_amount - ($amount * ($count - 1)), 4)
                : $amount;

            $rows[] = [
                "company_id" => $account->company_id,
                "purchase_account_payable_id" => $account->id,
                "installment_number" => $number,
                "due_date" => $firstDueDate ? Carbon::parse($firstDueDate)->addMonthsNoOverflow($number - 1)->toDateString() : null,
                "amount" => $lineAmount,
                "paid_amount" => 0,
                "pending_amount" => $lineAmount,
                "status" => "pending",
                "created_at" => now(),
                "created_by" => $userId
            ];
        }

        PurchasePayableInstallment::insert($rows);

    }

    private static function createSalePaymentTrace(SaleAccountReceivable $account, Collection $paymentLines, int $userId): void {

        if($paymentLines->isEmpty()) return;

        SaleReceivablePayment::insert($paymentLines
            ->map(fn($payment) => [
                "company_id" => $account->company_id,
                "sale_account_receivable_id" => $account->id,
                "payment_method_id" => $payment["payment_method_id"] ?? null,
                "payment_method_variant_id" => $payment["payment_method_variant_id"] ?? null,
                "paid_at" => now(),
                "amount" => $payment["amount"] ?? 0,
                "reference" => $payment["reference"] ?? null,
                "observation" => $payment["note"] ?? null,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $userId
            ])
            ->all());

    }

    private static function createPurchasePaymentTrace(PurchaseAccountPayable $account, Collection $paymentLines, int $userId): void {

        if($paymentLines->isEmpty()) return;

        PurchasePayablePayment::insert($paymentLines
            ->map(fn($payment) => [
                "company_id" => $account->company_id,
                "purchase_account_payable_id" => $account->id,
                "payment_method_id" => $payment["payment_method_id"] ?? null,
                "payment_method_variant_id" => $payment["payment_method_variant_id"] ?? null,
                "paid_at" => now(),
                "amount" => $payment["amount"] ?? 0,
                "reference" => $payment["reference"] ?? null,
                "observation" => $payment["note"] ?? null,
                "status" => "active",
                "created_at" => now(),
                "created_by" => $userId
            ])
            ->all());

    }

}
