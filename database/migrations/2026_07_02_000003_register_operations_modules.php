<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    private const MODULES = [
        104 => [
            "slug" => "sc_user-attendances",
            "name" => "user-attendances",
            "description" => "Controla ingresos, salidas y horas trabajadas por colaborador.",
            "order" => 6,
            "dom_id" => "menu-user-attendances",
            "dom_label" => "Asistencia del personal",
            "dom_route" => "user_attendances.index"
        ],
        105 => [
            "slug" => "sc_restaurant-pos",
            "name" => "restaurant-pos",
            "description" => "Gestiona mesas, pedidos en curso y su posterior cobro en POS.",
            "order" => 7,
            "dom_id" => "menu-restaurant-pos",
            "dom_label" => "Restaurante POS",
            "dom_route" => "restaurant_pos.index"
        ],
        106 => [
            "slug" => "sc_service-sessions",
            "name" => "service-sessions",
            "description" => "Inicia, asigna y finaliza servicios midiendo su tiempo real.",
            "order" => 8,
            "dom_id" => "menu-service-sessions",
            "dom_label" => "Servicios en curso",
            "dom_route" => "service_sessions.index"
        ]
    ];

    public function up(): void {

        if(!DB::table("sections")->where("id", 10)->exists()) return;

        foreach(self::MODULES as $id => $module) {
            DB::table("sub_sections")->updateOrInsert(
                ["id" => $id],
                ["section_id" => 10, ...$module]
            );
        }

        DB::table("companies")
            ->pluck("id")
            ->each(function($companyId) {
                foreach(array_keys(self::MODULES) as $moduleId) {
                    DB::table("companies_sub_sections")->updateOrInsert(
                        ["company_id" => $companyId, "sub_section_id" => $moduleId],
                        ["section_order" => 3, "sub_section_order" => self::MODULES[$moduleId]["order"]]
                    );
                }
            });

    }

    public function down(): void {

        $moduleIds = array_keys(self::MODULES);

        DB::table("companies_sub_sections")->whereIn("sub_section_id", $moduleIds)->delete();
        DB::table("role_sub_sections")->whereIn("sub_section_id", $moduleIds)->delete();
        DB::table("sub_sections")->whereIn("id", $moduleIds)->delete();

    }

};
