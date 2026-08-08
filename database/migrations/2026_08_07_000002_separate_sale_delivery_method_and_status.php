<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        if (! Schema::hasTable("sale_delivery_methods")) {
            Schema::create("sale_delivery_methods", function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("company_id");
                $table->string("code", 50);
                $table->string("name", 100);
                $table->string("description", 300)->nullable();
                $table->unsignedSmallInteger("sort_order")->default(0);
                $table->boolean("is_default")->default(false);
                $table->enum("status", ["active", "inactive"])->default("active");
                $table->timestamp("created_at")->useCurrent()->nullable();
                $table->integer("created_by")->nullable();
                $table->timestamp("updated_at")->nullable();
                $table->integer("updated_by")->nullable();

                $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
                $table->unique(["company_id", "code"], "sale_delivery_methods_company_code_uq");
                $table->index(["company_id", "status", "sort_order"], "sale_delivery_methods_company_status_idx");
            });
        }

        $this->seedDeliveryMethods();

        if (Schema::hasTable("sales_header") && ! Schema::hasColumn("sales_header", "delivery_method_id")) {
            Schema::table("sales_header", function (Blueprint $table) {
                $table->unsignedBigInteger("delivery_method_id")->nullable()->after("warehouse_id");
                $table->foreign("delivery_method_id", "sales_header_delivery_method_fk")
                    ->references("id")
                    ->on("sale_delivery_methods")
                    ->nullOnDelete();
                $table->index(["company_id", "delivery_method_id"], "sales_header_company_delivery_method_idx");
            });
        }

        $this->normalizeHistoricalDeliveryStatuses();
        $this->backfillPendingDeliveryTracking();

    }

    public function down(): void {

        if (Schema::hasTable("sales_header") && Schema::hasColumn("sales_header", "delivery_method_id")) {
            Schema::table("sales_header", function (Blueprint $table) {
                $table->dropForeign("sales_header_delivery_method_fk");
                $table->dropIndex("sales_header_company_delivery_method_idx");
                $table->dropColumn("delivery_method_id");
            });
        }

        Schema::dropIfExists("sale_delivery_methods");

    }

    private function seedDeliveryMethods(): void {

        if (! Schema::hasTable("companies") || ! Schema::hasTable("sale_delivery_methods")) {
            return;
        }

        $methods = [
            [
                "code" => "local_pickup",
                "name" => "Recojo en local",
                "description" => "El cliente recoge lo vendido en un local de la empresa.",
                "sort_order" => 10,
                "is_default" => true,
            ],
            [
                "code" => "delivery",
                "name" => "Delivery",
                "description" => "La empresa entrega lo vendido en la ubicación indicada por el cliente.",
                "sort_order" => 20,
                "is_default" => false,
            ],
            [
                "code" => "shipping",
                "name" => "Envío",
                "description" => "Lo vendido se remite mediante transporte propio o un tercero.",
                "sort_order" => 30,
                "is_default" => false,
            ],
        ];

        DB::table("companies")->orderBy("id")->pluck("id")->each(function ($companyId) use ($methods) {
            foreach ($methods as $method) {
                DB::table("sale_delivery_methods")->updateOrInsert(
                    ["company_id" => (int) $companyId, "code" => $method["code"]],
                    $method + [
                        "company_id" => (int) $companyId,
                        "status" => "active",
                        "created_at" => now(),
                    ]
                );
            }
        });

    }

    private function normalizeHistoricalDeliveryStatuses(): void {

        if (! Schema::hasTable("sales_header")
            || ! Schema::hasColumn("sales_header", "delivery_mode")
            || ! Schema::hasColumn("sales_header", "delivery_status")) {
            return;
        }

        DB::table("sales_header")
            ->where("delivery_mode", "immediate")
            ->where("delivery_status", "pending")
            ->update([
                "delivery_status" => "delivered",
                "delivered_at" => DB::raw("COALESCE(delivered_at, created_at)"),
            ]);

        DB::table("sales_header")
            ->where("delivery_mode", "immediate")
            ->where("delivery_status", "delivered")
            ->whereNull("delivered_at")
            ->update(["delivered_at" => DB::raw("created_at")]);

    }

    private function backfillPendingDeliveryTracking(): void {

        if (! Schema::hasTable("sales_header")
            || ! Schema::hasTable("sales_body")
            || ! Schema::hasTable("sale_deliveries")
            || ! Schema::hasTable("sale_delivery_items")) {
            return;
        }

        DB::table("sales_header")
            ->where("status", "active")
            ->where("delivery_status", "pending")
            ->whereNotExists(function ($query) {
                $query->selectRaw("1")
                    ->from("sale_deliveries")
                    ->whereColumn("sale_deliveries.sale_header_id", "sales_header.id");
            })
            ->orderBy("id")
            ->chunkById(200, function ($sales) {
                foreach ($sales as $sale) {
                    $items = DB::table("sales_body")
                        ->where("company_id", (int) $sale->company_id)
                        ->where("sale_header_id", (int) $sale->id)
                        ->where("type", "product")
                        ->where("status", "active")
                        ->get(["id", "item_id", "quantity"]);

                    if ($items->isEmpty()) {
                        continue;
                    }

                    $total = (float) $items->sum(fn ($item) => (float) $item->quantity);
                    $deliveryId = DB::table("sale_deliveries")->insertGetId([
                        "company_id" => (int) $sale->company_id,
                        "sale_header_id" => (int) $sale->id,
                        "warehouse_id" => $sale->warehouse_id,
                        "total_quantity" => $total,
                        "delivered_quantity" => 0,
                        "pending_quantity" => $total,
                        "status" => "pending",
                        "observation" => $sale->delivery_observation,
                        "created_at" => $sale->created_at ?? now(),
                        "created_by" => $sale->created_by,
                    ]);

                    DB::table("sale_delivery_items")->insert($items->map(fn ($item) => [
                        "company_id" => (int) $sale->company_id,
                        "sale_delivery_id" => $deliveryId,
                        "sale_body_id" => (int) $item->id,
                        "item_id" => (int) $item->item_id,
                        "quantity_ordered" => (float) $item->quantity,
                        "quantity_delivered" => 0,
                        "quantity_pending" => (float) $item->quantity,
                        "status" => "pending",
                        "created_at" => $sale->created_at ?? now(),
                        "created_by" => $sale->created_by,
                    ])->all());
                }
            }, "id");

    }
};
