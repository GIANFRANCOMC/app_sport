<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void {

        $companyIds = DB::table("companies")->pluck("id");

        foreach($companyIds as $companyId) {

            $this->syncTaxes((int) $companyId);
            $this->syncPaymentMethods((int) $companyId);
            $this->syncAttendanceSettings((int) $companyId);

        }

    }

    public function down(): void {

        $companyIds = DB::table("companies")->pluck("id");

        foreach($companyIds as $companyId) {

            DB::table("taxes")
                ->where("company_id", $companyId)
                ->whereIn("code", ["SALE-IGV", "SALE-ICBP", "PURCHASE-IGV", "PURCHASE-ICBP"])
                ->delete();

            DB::table("payment_methods")
                ->where("company_id", $companyId)
                ->whereIn("code", [
                    "CASH",
                    "BANK_DEPOSIT",
                    "BANK_TRANSFER",
                    "DEBIT_CARD",
                    "CREDIT_CARD",
                    "CHECK",
                    "DIGITAL_WALLET",
                    "YAPE",
                    "PLIN"
                ])
                ->delete();

            DB::table("company_settings")
                ->where("company_id", $companyId)
                ->where("group", "customer_attendance")
                ->where("key", "max_active_hours")
                ->delete();

        }

    }

    private function syncAttendanceSettings(int $companyId): void {

        DB::table("company_settings")->updateOrInsert(
            [
                "company_id" => $companyId,
                "group" => "customer_attendance",
                "key" => "max_active_hours"
            ],
            [
                "company_id" => $companyId,
                "group" => "customer_attendance",
                "key" => "max_active_hours",
                "value" => "20",
                "description" => "Horas maximas que una asistencia de cliente puede permanecer abierta. Si se supera, el backend finaliza la asistencia vencida y permite registrar una nueva.",
                "value_type" => "integer",
                "status" => "active"
            ]
        );

    }

    private function syncTaxes(int $companyId): void {

        $taxes = [
            [
                "code" => "SALE-IGV",
                "name" => "IGV",
                "description" => "Impuesto General a las Ventas del Perú aplicado a ventas. Si el item incluye IGV, se calcula como tributo contenido; si no lo incluye, se suma al total.",
                "scope" => "sale",
                "calculation_type" => "percentage",
                "rate" => 18,
                "min_apply_quantity" => null,
                "max_apply_quantity" => null,
                "operation_type" => "addition",
                "is_required" => true,
                "is_default" => true
            ],
            [
                "code" => "SALE-ICBP",
                "name" => "ICBP",
                "description" => "Impuesto al Consumo de Bolsas Plásticas aplicado a ventas cuando corresponde. Es opcional porque no todas las ventas incluyen bolsa gravada.",
                "scope" => "sale",
                "calculation_type" => "fixed",
                "rate" => 0.5,
                "min_apply_quantity" => 0,
                "max_apply_quantity" => null,
                "operation_type" => "addition",
                "is_required" => false,
                "is_default" => false
            ],
            [
                "code" => "PURCHASE-IGV",
                "name" => "IGV",
                "description" => "Impuesto General a las Ventas del Perú aplicado a compras. Se calcula sobre la base de compra registrada.",
                "scope" => "purchase",
                "calculation_type" => "percentage",
                "rate" => 18,
                "min_apply_quantity" => null,
                "max_apply_quantity" => null,
                "operation_type" => "addition",
                "is_required" => true,
                "is_default" => true
            ],
            [
                "code" => "PURCHASE-ICBP",
                "name" => "ICBP",
                "description" => "Impuesto al Consumo de Bolsas Plásticas aplicado a compras cuando corresponde. Es opcional porque no todas las compras incluyen bolsa gravada.",
                "scope" => "purchase",
                "calculation_type" => "fixed",
                "rate" => 0.5,
                "min_apply_quantity" => 0,
                "max_apply_quantity" => null,
                "operation_type" => "addition",
                "is_required" => false,
                "is_default" => false
            ]
        ];

        foreach($taxes as $tax) {

            DB::table("taxes")->updateOrInsert(
                ["company_id" => $companyId, "code" => $tax["code"]],
                $tax + ["company_id" => $companyId, "status" => "active"]
            );

        }

    }

    private function syncPaymentMethods(int $companyId): void {

        $methods = [
            ["code" => "CASH", "sunat_code" => "008", "name" => "Efectivo", "image_path" => "System/assets/img/payment-methods/cash.svg", "scope" => "both", "requires_reference" => false, "is_default" => true],
            ["code" => "BANK_DEPOSIT", "sunat_code" => "001", "name" => "Depósito en cuenta", "image_path" => "System/assets/img/payment-methods/bank-deposit.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["code" => "BANK_TRANSFER", "sunat_code" => "003", "name" => "Transferencia de fondos", "image_path" => "System/assets/img/payment-methods/bank-transfer.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["code" => "DEBIT_CARD", "sunat_code" => "005", "name" => "Tarjeta de débito", "image_path" => "System/assets/img/payment-methods/debit-card.svg", "scope" => "sale", "requires_reference" => true, "is_default" => false],
            ["code" => "CREDIT_CARD", "sunat_code" => "006", "name" => "Tarjeta de crédito", "image_path" => "System/assets/img/payment-methods/credit-card.svg", "scope" => "sale", "requires_reference" => true, "is_default" => false],
            ["code" => "CHECK", "sunat_code" => "007", "name" => "Cheque no negociable", "image_path" => "System/assets/img/payment-methods/check.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["code" => "DIGITAL_WALLET", "sunat_code" => null, "name" => "Billetera digital", "image_path" => "System/assets/img/payment-methods/digital-wallet.svg", "scope" => "both", "requires_reference" => true, "is_default" => false]
        ];

        foreach($methods as $method) {

            DB::table("payment_methods")->updateOrInsert(
                ["company_id" => $companyId, "code" => $method["code"]],
                $method + ["company_id" => $companyId, "status" => "active"]
            );

        }

    }

};
