<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        if(!Schema::hasTable("companies") || !Schema::hasTable("payment_methods") || !Schema::hasTable("payment_method_variants")) {
            return;
        }

        $companyIds = DB::table("companies")->pluck("id");

        foreach($companyIds as $companyId) {
            $this->syncDigitalWallet($companyId);
        }

    }

    public function down(): void {

        // La normalización evita duplicar billeteras como métodos raíz.
        // No se revierte para no recrear datos operativos obsoletos.

    }

    private function syncDigitalWallet(int $companyId): void {

        DB::table("payment_methods")->updateOrInsert(
            ["company_id" => $companyId, "code" => "DIGITAL_WALLET"],
            [
                "company_id" => $companyId,
                "code" => "DIGITAL_WALLET",
                "category" => "digital_wallet",
                "sunat_code" => null,
                "name" => "Billetera digital",
                "description" => "Método general para pagos con billeteras digitales como Yape, Plin, Agora PAY, Bim o IzipayYA.",
                "image_path" => "System/assets/img/payment-methods/digital-wallet.svg",
                "scope" => "both",
                "requires_reference" => true,
                "supports_variants" => true,
                "allows_partial_payment" => true,
                "is_default" => false,
                "status" => "active",
                "updated_at" => now()
            ]
        );

        $digitalWalletId = DB::table("payment_methods")
            ->where("company_id", $companyId)
            ->where("code", "DIGITAL_WALLET")
            ->value("id");

        if(!$digitalWalletId) {
            return;
        }

        foreach($this->digitalWalletVariants() as $variant) {
            DB::table("payment_method_variants")->updateOrInsert(
                ["company_id" => $companyId, "payment_method_id" => $digitalWalletId, "code" => $variant["code"]],
                $variant + [
                    "company_id" => $companyId,
                    "payment_method_id" => $digitalWalletId,
                    "sunat_code" => null,
                    "requires_reference" => true,
                    "is_default" => false,
                    "status" => "active",
                    "updated_at" => now()
                ]
            );
        }

        DB::table("payment_methods")
            ->where("company_id", $companyId)
            ->whereIn("code", ["YAPE", "PLIN"])
            ->delete();

    }

    private function digitalWalletVariants(): array {

        return [
            ["code" => "YAPE", "name" => "Yape", "image_path" => "System/assets/img/payment-methods/yape.svg", "description" => "Billetera digital de uso masivo en Perú."],
            ["code" => "PLIN", "name" => "Plin", "image_path" => "System/assets/img/payment-methods/plin.svg", "description" => "Billetera digital interoperable en Perú."],
            ["code" => "AGORA_PAY", "name" => "Agora PAY", "image_path" => "System/assets/img/payment-methods/agora-pay.svg", "description" => "Billetera digital disponible en Perú."],
            ["code" => "BIM", "name" => "Bim", "image_path" => "System/assets/img/payment-methods/bim.svg", "description" => "Billetera móvil peruana orientada a pagos digitales."],
            ["code" => "IZIPAYYA", "name" => "IzipayYA", "image_path" => "System/assets/img/payment-methods/izipayya.svg", "description" => "Billetera digital antes conocida como Tunki."]
        ];

    }

};
