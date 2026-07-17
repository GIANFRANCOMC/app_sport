<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        if(!Schema::hasTable("sections") || !Schema::hasTable("sub_sections")) {

            return;

        }

        $sections = [
            ["id" => 3, "slug" => "sc_sales", "name" => "sales", "order" => 3, "dom_id" => "menu-parent-sales", "dom_label" => "Ventas", "dom_icon" => "fa-solid fa-cash-register", "has_sub_menu" => true, "status" => "active"],
            ["id" => 10, "slug" => "sc_cash", "name" => "cash", "order" => 4, "dom_id" => "menu-parent-cash", "dom_label" => "Cajas", "dom_icon" => "fa-solid fa-vault", "has_sub_menu" => true, "status" => "active"],
            ["id" => 9, "slug" => "sc_purchases", "name" => "purchases", "order" => 5, "dom_id" => "menu-parent-purchases", "dom_label" => "Compras", "dom_icon" => "fa-solid fa-cart-flatbed", "has_sub_menu" => true, "status" => "active"],
            ["id" => 4, "slug" => "sc_customers", "name" => "customers", "order" => 6, "dom_id" => "menu-parent-customers", "dom_label" => "Gestión de clientes", "dom_icon" => "fa fa-user", "has_sub_menu" => true, "status" => "active"],
            ["id" => 12, "slug" => "sc_staff", "name" => "staff", "order" => 7, "dom_id" => "menu-parent-staff", "dom_label" => "Gestión de colaboradores", "dom_icon" => "fa-solid fa-users-gear", "has_sub_menu" => true, "status" => "active"],
            ["id" => 5, "slug" => "sc_items", "name" => "items", "order" => 8, "dom_id" => "menu-parent-items", "dom_label" => "Catálogo comercial", "dom_icon" => "fa fa-book", "has_sub_menu" => true, "status" => "active"],
            ["id" => 11, "slug" => "sc_inventory", "name" => "inventory", "order" => 9, "dom_id" => "menu-parent-inventory", "dom_label" => "Inventario", "dom_icon" => "fa-solid fa-boxes-stacked", "has_sub_menu" => true, "status" => "active"],
            ["id" => 13, "slug" => "sc_restaurant_services", "name" => "restaurant-services", "order" => 10, "dom_id" => "menu-parent-restaurant-services", "dom_label" => "Restaurante y servicios", "dom_icon" => "fa-solid fa-bell-concierge", "has_sub_menu" => true, "status" => "active"],
            ["id" => 6, "slug" => "sc_infrastructure", "name" => "infrastructure", "order" => 11, "dom_id" => "menu-parent-infrastructure", "dom_label" => "Infraestructura", "dom_icon" => "fa-solid fa-industry", "has_sub_menu" => true, "status" => "active"],
            ["id" => 7, "slug" => "sc_configuration", "name" => "configuration", "order" => 12, "dom_id" => "menu-parent-configuration", "dom_label" => "Configuración", "dom_icon" => "fa fa-gear", "has_sub_menu" => true, "status" => "active"],
            ["id" => 8, "slug" => "sc_reports", "name" => "reports", "order" => 13, "dom_id" => "menu-parent-reports", "dom_label" => "Reportes", "dom_icon" => "fa fa-print", "has_sub_menu" => false, "status" => "active"]
        ];

        foreach($sections as $section) {

            DB::table("sections")->updateOrInsert(["id" => $section["id"]], $section);

        }

        $this->moveSubSections([
            32 => ["section_id" => 3, "order" => 1],
            31 => ["section_id" => 3, "order" => 2],
            30 => ["section_id" => 3, "order" => 3],
            33 => ["section_id" => 3, "order" => 4],
            100 => ["section_id" => 10, "order" => 1],
            101 => ["section_id" => 10, "order" => 2],
            102 => ["section_id" => 10, "order" => 3],
            103 => ["section_id" => 10, "order" => 4],
            109 => ["section_id" => 10, "order" => 5],
            71 => ["section_id" => 12, "order" => 1, "description" => "Administra colaboradores internos, roles y accesos."],
            104 => ["section_id" => 12, "order" => 2],
            105 => ["section_id" => 13, "order" => 1],
            106 => ["section_id" => 13, "order" => 2],
            70 => ["section_id" => 7, "order" => 1],
            72 => ["section_id" => 7, "order" => 2],
            73 => ["section_id" => 7, "order" => 3],
            74 => ["section_id" => 7, "order" => 4]
        ]);

        $this->syncCompanyOrders();

    }

    public function down(): void {

        if(!Schema::hasTable("sections") || !Schema::hasTable("sub_sections")) {

            return;

        }

        DB::table("sections")->whereIn("id", [12, 13])->delete();

        DB::table("sections")->where("id", 10)->update([
            "slug" => "sc_operations",
            "name" => "operations",
            "order" => 3,
            "dom_id" => "menu-parent-operations",
            "dom_label" => "Operación",
            "dom_icon" => "fa-solid fa-bolt",
            "has_sub_menu" => true,
            "status" => "active"
        ]);

        DB::table("sections")->where("id", 3)->update(["order" => 4]);
        DB::table("sections")->where("id", 5)->update(["order" => 7]);
        DB::table("sections")->where("id", 6)->update(["order" => 9]);
        DB::table("sections")->where("id", 7)->update(["order" => 10]);
        DB::table("sections")->where("id", 8)->update(["order" => 11]);
        DB::table("sections")->where("id", 11)->update(["order" => 8]);

        $this->moveSubSections([
            32 => ["section_id" => 10, "order" => 1],
            30 => ["section_id" => 3, "order" => 1],
            31 => ["section_id" => 3, "order" => 2],
            100 => ["section_id" => 10, "order" => 2],
            101 => ["section_id" => 10, "order" => 3],
            102 => ["section_id" => 10, "order" => 4],
            103 => ["section_id" => 10, "order" => 5],
            104 => ["section_id" => 10, "order" => 6],
            105 => ["section_id" => 10, "order" => 7],
            106 => ["section_id" => 10, "order" => 8],
            71 => ["section_id" => 7, "order" => 2, "description" => "Administra usuarios internos, roles y accesos."],
            72 => ["section_id" => 7, "order" => 3],
            73 => ["section_id" => 7, "order" => 4]
        ]);

        $this->syncLegacyCompanyOrders();

    }

    private function moveSubSections(array $subSections): void {

        foreach($subSections as $subSectionId => $values) {

            DB::table("sub_sections")
                ->where("id", $subSectionId)
                ->update($values);

        }

    }

    private function syncCompanyOrders(): void {

        $sectionOrders = [
            20 => 2,
            32 => 3,
            31 => 3,
            30 => 3,
            33 => 3,
            100 => 4,
            101 => 4,
            102 => 4,
            103 => 4,
            109 => 4,
            90 => 5,
            92 => 5,
            91 => 5,
            40 => 6,
            41 => 6,
            42 => 6,
            43 => 6,
            44 => 6,
            45 => 6,
            71 => 7,
            104 => 7,
            50 => 8,
            51 => 8,
            52 => 8,
            53 => 8,
            55 => 8,
            59 => 8,
            54 => 9,
            56 => 9,
            57 => 9,
            107 => 9,
            58 => 9,
            105 => 10,
            106 => 10,
            60 => 11,
            61 => 11,
            62 => 11,
            63 => 11,
            70 => 12,
            72 => 12,
            73 => 12,
            74 => 12,
            80 => 13
        ];

        $subSectionOrders = [
            20 => 1,
            32 => 1,
            31 => 2,
            30 => 3,
            33 => 4,
            100 => 1,
            101 => 2,
            102 => 3,
            103 => 4,
            109 => 5,
            90 => 1,
            92 => 2,
            91 => 3,
            40 => 1,
            41 => 2,
            42 => 3,
            43 => 4,
            44 => 5,
            45 => 6,
            71 => 1,
            104 => 2,
            50 => 1,
            51 => 2,
            52 => 3,
            53 => 4,
            55 => 5,
            59 => 6,
            54 => 1,
            56 => 2,
            57 => 3,
            107 => 4,
            58 => 5,
            105 => 1,
            106 => 2,
            60 => 1,
            61 => 2,
            62 => 3,
            63 => 4,
            70 => 1,
            72 => 2,
            73 => 3,
            74 => 4,
            80 => 1
        ];

        foreach($sectionOrders as $subSectionId => $sectionOrder) {

            DB::table("companies_sub_sections")
                ->where("sub_section_id", $subSectionId)
                ->update(["section_order" => $sectionOrder]);

        }

        foreach($subSectionOrders as $subSectionId => $subSectionOrder) {

            DB::table("companies_sub_sections")
                ->where("sub_section_id", $subSectionId)
                ->update(["sub_section_order" => $subSectionOrder]);

        }

    }

    private function syncLegacyCompanyOrders(): void {

        $sectionOrders = [
            20 => 2,
            32 => 3,
            100 => 3,
            101 => 3,
            102 => 3,
            103 => 3,
            104 => 3,
            105 => 3,
            106 => 3,
            30 => 4,
            31 => 4,
            90 => 5,
            92 => 5,
            91 => 5,
            40 => 6,
            41 => 6,
            42 => 6,
            43 => 6,
            44 => 6,
            45 => 6,
            50 => 7,
            51 => 7,
            52 => 7,
            53 => 7,
            55 => 7,
            59 => 7,
            54 => 8,
            56 => 8,
            57 => 8,
            107 => 8,
            58 => 8,
            60 => 9,
            61 => 9,
            62 => 9,
            63 => 9,
            70 => 10,
            71 => 10,
            72 => 10,
            73 => 10,
            80 => 11
        ];

        foreach($sectionOrders as $subSectionId => $sectionOrder) {

            DB::table("companies_sub_sections")
                ->where("sub_section_id", $subSectionId)
                ->update(["section_order" => $sectionOrder]);

        }

    }

};
