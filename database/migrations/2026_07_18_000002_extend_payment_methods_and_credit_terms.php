<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        $this->extendPaymentMethods();
        $this->createPaymentMethodVariants();
        $this->extendCommercialDocuments();
        $this->createAccountsReceivable();
        $this->createAccountsPayable();
        $this->syncReferenceData();

    }

    public function down(): void {

        Schema::dropIfExists("purchase_payable_payments");
        Schema::dropIfExists("purchase_payable_installments");
        Schema::dropIfExists("purchase_accounts_payable");
        Schema::dropIfExists("sale_receivable_payments");
        Schema::dropIfExists("sale_receivable_installments");
        Schema::dropIfExists("sale_accounts_receivable");

        if(Schema::hasTable("purchase_payments") && Schema::hasColumn("purchase_payments", "payment_method_variant_id")) {
            Schema::table("purchase_payments", function(Blueprint $table) {
                $table->dropForeign(["payment_method_variant_id"]);
                $table->dropColumn("payment_method_variant_id");
            });
        }

        if(Schema::hasTable("sale_payments") && Schema::hasColumn("sale_payments", "payment_method_variant_id")) {
            Schema::table("sale_payments", function(Blueprint $table) {
                $table->dropForeign(["payment_method_variant_id"]);
                $table->dropColumn("payment_method_variant_id");
            });
        }

        if(Schema::hasTable("purchase_headers")) {
            Schema::table("purchase_headers", function(Blueprint $table) {
                foreach(["payment_modality", "installment_extra_percentage", "installment_extra_amount"] as $column) {
                    if(Schema::hasColumn("purchase_headers", $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if(Schema::hasTable("sales_header")) {
            Schema::table("sales_header", function(Blueprint $table) {
                foreach(["payment_modality", "installment_extra_percentage", "installment_extra_amount", "paid_amount", "balance_due", "payment_status"] as $column) {
                    if(Schema::hasColumn("sales_header", $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists("payment_method_variants");

        if(Schema::hasTable("payment_methods")) {
            Schema::table("payment_methods", function(Blueprint $table) {
                foreach(["category", "description", "supports_variants", "allows_partial_payment"] as $column) {
                    if(Schema::hasColumn("payment_methods", $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        DB::table("company_settings")
            ->whereIn("group", ["sales", "purchases"])
            ->whereIn("key", ["default_payment_modality", "installment_extra_percentage"])
            ->delete();

    }

    private function extendPaymentMethods(): void {

        Schema::table("payment_methods", function(Blueprint $table) {
            if(!Schema::hasColumn("payment_methods", "category")) {
                $table->string("category", 40)->default("other")->after("name");
            }

            if(!Schema::hasColumn("payment_methods", "description")) {
                $table->text("description")->nullable()->after("sunat_code");
            }

            if(!Schema::hasColumn("payment_methods", "supports_variants")) {
                $table->boolean("supports_variants")->default(false)->after("requires_reference");
            }

            if(!Schema::hasColumn("payment_methods", "allows_partial_payment")) {
                $table->boolean("allows_partial_payment")->default(true)->after("supports_variants");
            }
        });

    }

    private function createPaymentMethodVariants(): void {

        if(Schema::hasTable("payment_method_variants")) return;

        Schema::create("payment_method_variants", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("payment_method_id");
            $table->string("code", 40);
            $table->string("name", 150);
            $table->string("sunat_code", 10)->nullable();
            $table->string("image_path", 500)->nullable();
            $table->text("description")->nullable();
            $table->boolean("requires_reference")->default(true);
            $table->boolean("is_default")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("payment_method_id")->references("id")->on("payment_methods")->onDelete("cascade");
        });

    }

    private function extendCommercialDocuments(): void {

        Schema::table("sales_header", function(Blueprint $table) {
            if(!Schema::hasColumn("sales_header", "payment_modality")) {
                $table->enum("payment_modality", ["paid_now", "cash_on_delivery", "installments"])->default("paid_now")->after("delivery_observation");
            }

            if(!Schema::hasColumn("sales_header", "installment_extra_percentage")) {
                $table->decimal("installment_extra_percentage", 16, 4)->default(0)->after("payment_modality");
            }

            if(!Schema::hasColumn("sales_header", "installment_extra_amount")) {
                $table->decimal("installment_extra_amount", 16, 4)->default(0)->after("installment_extra_percentage");
            }

            if(!Schema::hasColumn("sales_header", "paid_amount")) {
                $table->decimal("paid_amount", 16, 4)->default(0)->after("total");
            }

            if(!Schema::hasColumn("sales_header", "balance_due")) {
                $table->decimal("balance_due", 16, 4)->default(0)->after("paid_amount");
            }

            if(!Schema::hasColumn("sales_header", "payment_status")) {
                $table->enum("payment_status", ["unpaid", "partial", "paid", "overpaid"])->default("paid")->after("balance_due");
            }
        });

        Schema::table("purchase_headers", function(Blueprint $table) {
            if(!Schema::hasColumn("purchase_headers", "payment_modality")) {
                $table->enum("payment_modality", ["paid_now", "cash_on_delivery", "installments"])->default("paid_now")->after("delivery_mode");
            }

            if(!Schema::hasColumn("purchase_headers", "installment_extra_percentage")) {
                $table->decimal("installment_extra_percentage", 16, 4)->default(0)->after("payment_modality");
            }

            if(!Schema::hasColumn("purchase_headers", "installment_extra_amount")) {
                $table->decimal("installment_extra_amount", 16, 4)->default(0)->after("installment_extra_percentage");
            }
        });

        Schema::table("sale_payments", function(Blueprint $table) {
            if(!Schema::hasColumn("sale_payments", "payment_method_variant_id")) {
                $table->unsignedBigInteger("payment_method_variant_id")->nullable()->after("payment_method_id");
                $table->foreign("payment_method_variant_id")->references("id")->on("payment_method_variants")->nullOnDelete();
            }
        });

        Schema::table("purchase_payments", function(Blueprint $table) {
            if(!Schema::hasColumn("purchase_payments", "payment_method_variant_id")) {
                $table->unsignedBigInteger("payment_method_variant_id")->nullable()->after("payment_method_id");
                $table->foreign("payment_method_variant_id")->references("id")->on("payment_method_variants")->nullOnDelete();
            }
        });

    }

    private function createAccountsReceivable(): void {

        if(!Schema::hasTable("sale_accounts_receivable")) {
            Schema::create("sale_accounts_receivable", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("sale_header_id");
                $table->unsignedBigInteger("customer_id");
                $table->unsignedBigInteger("currency_id");
                $table->date("issue_date");
                $table->date("due_date")->nullable();
                $table->enum("payment_modality", ["cash_on_delivery", "installments"]);
                $table->decimal("original_amount", 16, 4)->default(0);
                $table->decimal("extra_percentage", 16, 4)->default(0);
                $table->decimal("extra_amount", 16, 4)->default(0);
                $table->decimal("total_amount", 16, 4)->default(0);
                $table->decimal("paid_amount", 16, 4)->default(0);
                $table->decimal("pending_amount", 16, 4)->default(0);
                $table->enum("status", ["pending", "partial", "paid", "overdue", "canceled"])->default("pending");
                $table->string("observation", 500)->nullable();
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();
                $table->timestamp("canceled_at")->nullable();
                $table->integer("canceled_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("sale_header_id")->references("id")->on("sales_header")->onDelete("cascade");
                $table->foreign("customer_id")->references("id")->on("customers")->restrictOnDelete();
                $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
            });
        }

        if(!Schema::hasTable("sale_receivable_installments")) {
            Schema::create("sale_receivable_installments", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("sale_account_receivable_id");
                $table->unsignedInteger("installment_number");
                $table->date("due_date")->nullable();
                $table->decimal("amount", 16, 4)->default(0);
                $table->decimal("paid_amount", 16, 4)->default(0);
                $table->decimal("pending_amount", 16, 4)->default(0);
                $table->enum("status", ["pending", "partial", "paid", "overdue", "canceled"])->default("pending");
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("sale_account_receivable_id", "fk_sale_recv_inst_account")->references("id")->on("sale_accounts_receivable")->onDelete("cascade");
            });
        }

        if(!Schema::hasTable("sale_receivable_payments")) {
            Schema::create("sale_receivable_payments", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("sale_account_receivable_id");
                $table->unsignedBigInteger("payment_method_id")->nullable();
                $table->unsignedBigInteger("payment_method_variant_id")->nullable();
                $table->dateTime("paid_at");
                $table->decimal("amount", 16, 4)->default(0);
                $table->string("reference", 100)->nullable();
                $table->string("observation", 500)->nullable();
                $table->enum("status", ["active", "canceled"])->default("active");
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("sale_account_receivable_id", "fk_sale_recv_pay_account")->references("id")->on("sale_accounts_receivable")->onDelete("cascade");
                $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
                $table->foreign("payment_method_variant_id")->references("id")->on("payment_method_variants")->nullOnDelete();
            });
        }

    }

    private function createAccountsPayable(): void {

        if(!Schema::hasTable("purchase_accounts_payable")) {
            Schema::create("purchase_accounts_payable", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("purchase_header_id");
                $table->unsignedBigInteger("supplier_id");
                $table->unsignedBigInteger("currency_id");
                $table->date("issue_date");
                $table->date("due_date")->nullable();
                $table->enum("payment_modality", ["cash_on_delivery", "installments"]);
                $table->decimal("original_amount", 16, 4)->default(0);
                $table->decimal("extra_percentage", 16, 4)->default(0);
                $table->decimal("extra_amount", 16, 4)->default(0);
                $table->decimal("total_amount", 16, 4)->default(0);
                $table->decimal("paid_amount", 16, 4)->default(0);
                $table->decimal("pending_amount", 16, 4)->default(0);
                $table->enum("status", ["pending", "partial", "paid", "overdue", "canceled"])->default("pending");
                $table->string("observation", 500)->nullable();
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();
                $table->timestamp("canceled_at")->nullable();
                $table->integer("canceled_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("purchase_header_id")->references("id")->on("purchase_headers")->onDelete("cascade");
                $table->foreign("supplier_id")->references("id")->on("suppliers")->restrictOnDelete();
                $table->foreign("currency_id")->references("id")->on("currencies")->restrictOnDelete();
            });
        }

        if(!Schema::hasTable("purchase_payable_installments")) {
            Schema::create("purchase_payable_installments", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("purchase_account_payable_id");
                $table->unsignedInteger("installment_number");
                $table->date("due_date")->nullable();
                $table->decimal("amount", 16, 4)->default(0);
                $table->decimal("paid_amount", 16, 4)->default(0);
                $table->decimal("pending_amount", 16, 4)->default(0);
                $table->enum("status", ["pending", "partial", "paid", "overdue", "canceled"])->default("pending");
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("purchase_account_payable_id", "fk_purchase_pay_inst_account")->references("id")->on("purchase_accounts_payable")->onDelete("cascade");
            });
        }

        if(!Schema::hasTable("purchase_payable_payments")) {
            Schema::create("purchase_payable_payments", function(Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->unsignedBigInteger("purchase_account_payable_id");
                $table->unsignedBigInteger("payment_method_id")->nullable();
                $table->unsignedBigInteger("payment_method_variant_id")->nullable();
                $table->dateTime("paid_at");
                $table->decimal("amount", 16, 4)->default(0);
                $table->string("reference", 100)->nullable();
                $table->string("observation", 500)->nullable();
                $table->enum("status", ["active", "canceled"])->default("active");
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->foreign("purchase_account_payable_id", "fk_purchase_pay_pay_account")->references("id")->on("purchase_accounts_payable")->onDelete("cascade");
                $table->foreign("payment_method_id")->references("id")->on("payment_methods")->nullOnDelete();
                $table->foreign("payment_method_variant_id")->references("id")->on("payment_method_variants")->nullOnDelete();
            });
        }

    }

    private function syncReferenceData(): void {

        foreach(DB::table("companies")->pluck("id") as $companyId) {
            $companyId = (int) $companyId;
            $this->syncPaymentMethods($companyId);
            $this->syncPaymentMethodVariants($companyId);
            $this->syncPaymentSettings($companyId);
        }

    }

    private function syncPaymentMethods(int $companyId): void {

        $methods = [
            ["code" => "CASH", "category" => "cash", "sunat_code" => "008", "name" => "Efectivo", "description" => "Pago realizado con dinero físico al momento de la operación.", "image_path" => "System/assets/img/payment-methods/cash.svg", "scope" => "both", "requires_reference" => false, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => true],
            ["code" => "BANK_DEPOSIT", "category" => "bank", "sunat_code" => "001", "name" => "Depósito en cuenta", "description" => "Depósito realizado en una cuenta bancaria de la empresa o del proveedor.", "image_path" => "System/assets/img/payment-methods/bank-deposit.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "MONEY_ORDER", "category" => "bank", "sunat_code" => "002", "name" => "Giro", "description" => "Giro u orden bancaria reconocida como medio de pago.", "image_path" => "System/assets/img/payment-methods/money-order.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "BANK_TRANSFER", "category" => "bank", "sunat_code" => "003", "name" => "Transferencia de fondos", "description" => "Transferencia bancaria entre cuentas o entidades financieras.", "image_path" => "System/assets/img/payment-methods/bank-transfer.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "PAYMENT_ORDER", "category" => "bank", "sunat_code" => "004", "name" => "Orden de pago", "description" => "Orden emitida mediante el sistema financiero para cancelar una operación.", "image_path" => "System/assets/img/payment-methods/payment-order.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "DEBIT_CARD", "category" => "card", "sunat_code" => "005", "name" => "Tarjeta de débito", "description" => "Pago con tarjeta de débito; puede registrar marca o red como variante.", "image_path" => "System/assets/img/payment-methods/debit-card.svg", "scope" => "sale", "requires_reference" => true, "supports_variants" => true, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "CREDIT_CARD", "category" => "card", "sunat_code" => "006", "name" => "Tarjeta de crédito", "description" => "Pago con tarjeta de crédito; puede registrar marca o red como variante.", "image_path" => "System/assets/img/payment-methods/credit-card.svg", "scope" => "sale", "requires_reference" => true, "supports_variants" => true, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "CHECK", "category" => "bank", "sunat_code" => "007", "name" => "Cheque no negociable", "description" => "Cheque emitido como medio de pago bancarizado.", "image_path" => "System/assets/img/payment-methods/check.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "DIGITAL_WALLET", "category" => "digital_wallet", "sunat_code" => null, "name" => "Billetera digital", "description" => "Método general para pagos con billeteras digitales como Yape, Plin, Agora PAY, Bim o IzipayYA.", "image_path" => "System/assets/img/payment-methods/digital-wallet.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => true, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "REMITTANCE", "category" => "bank", "sunat_code" => null, "name" => "Remesa", "description" => "Remesa canalizada por el sistema financiero.", "image_path" => "System/assets/img/payment-methods/remittance.svg", "scope" => "both", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false],
            ["code" => "LETTER_OF_CREDIT", "category" => "bank", "sunat_code" => null, "name" => "Carta de crédito", "description" => "Carta de crédito usada principalmente en compras u operaciones empresariales.", "image_path" => "System/assets/img/payment-methods/letter-of-credit.svg", "scope" => "purchase", "requires_reference" => true, "supports_variants" => false, "allows_partial_payment" => true, "is_default" => false]
        ];

        foreach($methods as $method) {
            DB::table("payment_methods")->updateOrInsert(
                ["company_id" => $companyId, "code" => $method["code"]],
                $method + ["company_id" => $companyId, "status" => "active"]
            );
        }

        DB::table("payment_methods")
            ->where("company_id", $companyId)
            ->whereIn("code", ["YAPE", "PLIN"])
            ->update([
                "status" => "inactive",
                "description" => "Migrado a variante de Billetera digital para mantener métodos generales y variantes específicas separadas.",
                "updated_at" => now()
            ]);

    }

    private function syncPaymentMethodVariants(int $companyId): void {

        $methods = DB::table("payment_methods")
            ->where("company_id", $companyId)
            ->whereIn("code", ["DIGITAL_WALLET", "DEBIT_CARD", "CREDIT_CARD"])
            ->pluck("id", "code");

        $variantsByMethod = [
            "DIGITAL_WALLET" => [
                ["code" => "YAPE", "name" => "Yape", "image_path" => "System/assets/img/payment-methods/yape.svg", "description" => "Billetera digital de uso masivo en Perú."],
                ["code" => "PLIN", "name" => "Plin", "image_path" => "System/assets/img/payment-methods/plin.svg", "description" => "Billetera digital interoperable en Perú."],
                ["code" => "AGORA_PAY", "name" => "Agora PAY", "image_path" => "System/assets/img/payment-methods/agora-pay.svg", "description" => "Billetera digital disponible en Perú."],
                ["code" => "BIM", "name" => "Bim", "image_path" => "System/assets/img/payment-methods/bim.svg", "description" => "Billetera móvil peruana orientada a pagos digitales."],
                ["code" => "IZIPAYYA", "name" => "IzipayYA", "image_path" => "System/assets/img/payment-methods/izipayya.svg", "description" => "Billetera digital antes conocida como Tunki."]
            ],
            "DEBIT_CARD" => [
                ["code" => "VISA_DEBIT", "name" => "Visa débito", "image_path" => "System/assets/img/payment-methods/visa.svg", "description" => "Pago con tarjeta de débito Visa."],
                ["code" => "MASTERCARD_DEBIT", "name" => "Mastercard débito", "image_path" => "System/assets/img/payment-methods/mastercard.svg", "description" => "Pago con tarjeta de débito Mastercard."]
            ],
            "CREDIT_CARD" => [
                ["code" => "VISA_CREDIT", "name" => "Visa crédito", "image_path" => "System/assets/img/payment-methods/visa.svg", "description" => "Pago con tarjeta de crédito Visa."],
                ["code" => "MASTERCARD_CREDIT", "name" => "Mastercard crédito", "image_path" => "System/assets/img/payment-methods/mastercard.svg", "description" => "Pago con tarjeta de crédito Mastercard."],
                ["code" => "AMEX_CREDIT", "name" => "American Express", "image_path" => "System/assets/img/payment-methods/american-express.svg", "description" => "Pago con tarjeta American Express."],
                ["code" => "DINERS_CREDIT", "name" => "Diners Club", "image_path" => "System/assets/img/payment-methods/diners-club.svg", "description" => "Pago con tarjeta Diners Club."]
            ]
        ];

        foreach($variantsByMethod as $methodCode => $variants) {
            $methodId = $methods[$methodCode] ?? null;
            if(!$methodId) continue;

            foreach($variants as $variant) {
                DB::table("payment_method_variants")->updateOrInsert(
                    ["company_id" => $companyId, "payment_method_id" => $methodId, "code" => $variant["code"]],
                    $variant + [
                        "company_id" => $companyId,
                        "payment_method_id" => $methodId,
                        "sunat_code" => null,
                        "requires_reference" => true,
                        "is_default" => false,
                        "status" => "active",
                        "updated_at" => now()
                    ]
                );
            }
        }

    }

    private function syncPaymentSettings(int $companyId): void {

        $settings = [
            [
                "group" => "sales",
                "key" => "default_payment_modality",
                "value" => "paid_now",
                "description" => "Modalidad de pago sugerida por defecto al registrar una venta. Valores: paid_now, cash_on_delivery o installments.",
                "value_type" => "string"
            ],
            [
                "group" => "sales",
                "key" => "installment_extra_percentage",
                "value" => "0",
                "description" => "Porcentaje adicional aplicado al total de una venta cuando la modalidad de pago es por cuotas.",
                "value_type" => "decimal"
            ],
            [
                "group" => "purchases",
                "key" => "default_payment_modality",
                "value" => "paid_now",
                "description" => "Modalidad de pago sugerida por defecto al registrar una compra. Valores: paid_now, cash_on_delivery o installments.",
                "value_type" => "string"
            ],
            [
                "group" => "purchases",
                "key" => "installment_extra_percentage",
                "value" => "0",
                "description" => "Porcentaje adicional aplicado al total de una compra cuando la modalidad de pago es por cuotas.",
                "value_type" => "decimal"
            ]
        ];

        foreach($settings as $setting) {
            DB::table("company_settings")->updateOrInsert(
                ["company_id" => $companyId, "group" => $setting["group"], "key" => $setting["key"]],
                $setting + ["company_id" => $companyId, "status" => "active"]
            );
        }

    }

};
