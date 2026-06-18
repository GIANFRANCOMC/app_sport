<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\System\Catalogs\{Brand, Category, CategoryItem, Item};
use App\Models\System\Warehouses\{InventoryMovement, Warehouse, WarehouseItem};

class CommercialCatalogSeeder extends Seeder {

    private const COMPANY_ID = 1;
    private const USER_ID = 1;
    private const CURRENCY_ID = 1;

    public function run(): void {

        DB::transaction(function() {

            $brands = $this->seedBrands();
            $categories = $this->seedCategories();

            foreach($this->items($brands) as $payload) {

                $item = $this->upsertItem($payload);
                $this->syncCategories($item, $payload["categories"], $categories);

                if($payload["type"] === "product") {

                    $this->syncInventory($item, $payload["inventory"]);

                }

            }

        });

    }

    private function seedBrands(): array {

        $records = [
            ["internal_code" => "MAR-HOLA", "name" => "Hola", "description" => "Marca demo para bebidas y productos de consumo."],
            ["internal_code" => "MAR-GYMPE", "name" => "Gympe", "description" => "Marca propia para productos deportivos."],
            ["internal_code" => "MAR-WELL", "name" => "Wellness", "description" => "Marca demo para bienestar y cuidado personal."]
        ];

        return collect($records)
            ->mapWithKeys(function(array $record) {

                $brand = Brand::updateOrCreate(
                    [
                        "company_id" => self::COMPANY_ID,
                        "internal_code" => $record["internal_code"]
                    ],
                    [
                        "name" => $record["name"],
                        "description" => $record["description"],
                        "status" => "active",
                        "created_by" => self::USER_ID,
                        "updated_by" => self::USER_ID
                    ]
                );

                return [$record["name"] => $brand];

            })
            ->all();

    }

    private function seedCategories(): array {

        $records = [
            ["internal_code" => "CAT-BEB", "name" => "Bebidas", "description" => "Productos líquidos para venta rápida."],
            ["internal_code" => "CAT-SUP", "name" => "Suplementos", "description" => "Proteínas, barras y complementos deportivos."],
            ["internal_code" => "CAT-ACC", "name" => "Accesorios", "description" => "Implementos y artículos de entrenamiento."],
            ["internal_code" => "CAT-SER", "name" => "Servicios", "description" => "Servicios presenciales o personalizados."],
            ["internal_code" => "CAT-MEM", "name" => "Membresías", "description" => "Planes comerciales con vigencia."]
        ];

        return collect($records)
            ->mapWithKeys(function(array $record) {

                $category = Category::updateOrCreate(
                    [
                        "company_id" => self::COMPANY_ID,
                        "internal_code" => $record["internal_code"]
                    ],
                    [
                        "name" => $record["name"],
                        "description" => $record["description"],
                        "status" => "active",
                        "created_by" => self::USER_ID,
                        "updated_by" => self::USER_ID
                    ]
                );

                return [$record["name"] => $category];

            })
            ->all();

    }

    private function items(array $brands): array {

        return [
            [
                "internal_code" => "PRO-AGUA625",
                "barcode" => "2000000001001",
                "name" => "Agua mineral 625 ml",
                "description" => "Bebida individual para venta en mostrador.",
                "price" => 2.50,
                "min_price" => 2.00,
                "max_price" => 3.50,
                "brand_id" => $brands["Hola"]->id ?? null,
                "type" => "product",
                "categories" => ["Bebidas"],
                "inventory" => ["quantity" => 36, "minimum_stock" => 8, "average_cost" => 1.20]
            ],
            [
                "internal_code" => "PRO-ISO500",
                "barcode" => "2000000001002",
                "name" => "Bebida isotónica 500 ml",
                "description" => "Reposición rápida para entrenamientos intensos.",
                "price" => 5.00,
                "min_price" => 4.50,
                "max_price" => 6.50,
                "brand_id" => $brands["Hola"]->id ?? null,
                "type" => "product",
                "categories" => ["Bebidas"],
                "inventory" => ["quantity" => 24, "minimum_stock" => 6, "average_cost" => 2.70]
            ],
            [
                "internal_code" => "PRO-WHEY1KG",
                "barcode" => "2000000001003",
                "name" => "Proteína whey 1 kg",
                "description" => "Suplemento demo de alta rotación.",
                "price" => 145.00,
                "min_price" => 130.00,
                "max_price" => 170.00,
                "brand_id" => $brands["Gympe"]->id ?? null,
                "type" => "product",
                "categories" => ["Suplementos"],
                "inventory" => ["quantity" => 10, "minimum_stock" => 3, "average_cost" => 95.00]
            ],
            [
                "internal_code" => "PRO-BARRA",
                "barcode" => "2000000001004",
                "name" => "Barra proteica",
                "description" => "Snack listo para vender en POS.",
                "price" => 8.00,
                "min_price" => 7.00,
                "max_price" => 10.00,
                "brand_id" => $brands["Gympe"]->id ?? null,
                "type" => "product",
                "categories" => ["Suplementos"],
                "inventory" => ["quantity" => 30, "minimum_stock" => 10, "average_cost" => 4.80]
            ],
            [
                "internal_code" => "PRO-TOALLA",
                "barcode" => "2000000001005",
                "name" => "Toalla deportiva",
                "description" => "Accesorio básico para entrenamiento.",
                "price" => 25.00,
                "min_price" => 22.00,
                "max_price" => 35.00,
                "brand_id" => $brands["Wellness"]->id ?? null,
                "type" => "product",
                "categories" => ["Accesorios"],
                "inventory" => ["quantity" => 15, "minimum_stock" => 4, "average_cost" => 14.00]
            ],
            [
                "internal_code" => "SER-EVAL",
                "barcode" => null,
                "name" => "Evaluación física",
                "description" => "Servicio inicial para medir condición y objetivos.",
                "price" => 35.00,
                "min_price" => 30.00,
                "max_price" => 50.00,
                "brand_id" => null,
                "type" => "service",
                "categories" => ["Servicios"],
                "inventory" => []
            ],
            [
                "internal_code" => "SER-PT60",
                "barcode" => null,
                "name" => "Entrenamiento personal 60 min",
                "description" => "Sesión personalizada por entrenador.",
                "price" => 70.00,
                "min_price" => 60.00,
                "max_price" => 90.00,
                "brand_id" => null,
                "type" => "service",
                "categories" => ["Servicios"],
                "inventory" => []
            ],
            [
                "internal_code" => "MEM-MENSUAL",
                "barcode" => null,
                "name" => "Membresía mensual",
                "description" => "Acceso mensual al gimnasio.",
                "price" => 120.00,
                "min_price" => 100.00,
                "max_price" => 150.00,
                "brand_id" => null,
                "type" => "subscription",
                "duration_type" => "month",
                "duration_value" => 1,
                "categories" => ["Membresías"],
                "inventory" => []
            ],
            [
                "internal_code" => "MEM-TRIMESTRAL",
                "barcode" => null,
                "name" => "Membresía trimestral",
                "description" => "Acceso por tres meses con tarifa preferencial.",
                "price" => 320.00,
                "min_price" => 290.00,
                "max_price" => 390.00,
                "brand_id" => null,
                "type" => "subscription",
                "duration_type" => "month",
                "duration_value" => 3,
                "categories" => ["Membresías"],
                "inventory" => []
            ]
        ];

    }

    private function upsertItem(array $payload): Item {

        return Item::updateOrCreate(
            [
                "company_id" => self::COMPANY_ID,
                "internal_code" => $payload["internal_code"]
            ],
            [
                "brand_id" => $payload["brand_id"] ?? null,
                "barcode" => $payload["barcode"] ?? null,
                "name" => $payload["name"],
                "description" => $payload["description"],
                "price" => $payload["price"],
                "price_includes_tax" => true,
                "min_price" => $payload["min_price"],
                "max_price" => $payload["max_price"],
                "currency_id" => self::CURRENCY_ID,
                "type" => $payload["type"],
                "duration_type" => $payload["duration_type"] ?? null,
                "duration_value" => $payload["duration_value"] ?? null,
                "see_my_web" => true,
                "see_my_web_price" => true,
                "status" => "active",
                "created_by" => self::USER_ID,
                "updated_by" => self::USER_ID
            ]
        );

    }

    private function syncCategories(Item $item, array $categoryNames, array $categories): void {

        foreach($categoryNames as $categoryName) {

            $category = $categories[$categoryName] ?? null;

            if(!$category) continue;

            CategoryItem::updateOrCreate(
                [
                    "category_id" => $category->id,
                    "item_id" => $item->id
                ],
                [
                    "status" => "active",
                    "created_by" => self::USER_ID,
                    "updated_by" => self::USER_ID
                ]
            );

        }

    }

    private function syncInventory(Item $item, array $inventory): void {

        $warehouses = Warehouse::query()
                               ->where("status", "active")
                               ->whereHas("branch", fn($query) => $query->where("company_id", self::COMPANY_ID))
                               ->get();

        foreach($warehouses as $warehouse) {

            $quantity = (float) ($inventory["quantity"] ?? 0);
            $minimumStock = (float) ($inventory["minimum_stock"] ?? 0);
            $averageCost = (float) ($inventory["average_cost"] ?? 0);
            $inventoryValue = round($quantity * $averageCost, 2);

            WarehouseItem::updateOrCreate(
                [
                    "warehouse_id" => $warehouse->id,
                    "item_id" => $item->id
                ],
                [
                    "quantity" => $quantity,
                    "minimum_stock" => $minimumStock,
                    "average_cost" => $averageCost,
                    "inventory_value" => $inventoryValue,
                    "status" => "active",
                    "created_by" => self::USER_ID,
                    "updated_by" => self::USER_ID
                ]
            );

            InventoryMovement::updateOrCreate(
                [
                    "warehouse_id" => $warehouse->id,
                    "item_id" => $item->id,
                    "origin_type" => "commercial_catalog_seeder",
                    "origin_id" => $item->id
                ],
                [
                    "company_id" => self::COMPANY_ID,
                    "user_id" => self::USER_ID,
                    "movement_type" => "initial_stock",
                    "quantity_before" => 0,
                    "quantity_change" => $quantity,
                    "quantity_after" => $quantity,
                    "unit_cost" => $averageCost,
                    "value_before" => 0,
                    "value_change" => $inventoryValue,
                    "value_after" => $inventoryValue,
                    "reason" => "Stock inicial generado por seeder comercial.",
                    "metadata" => [
                        "reference" => "SEED-CATALOG-{$item->internal_code}",
                        "source" => "CommercialCatalogSeeder"
                    ],
                    "created_at" => now()
                ]
            );

        }

    }

}
