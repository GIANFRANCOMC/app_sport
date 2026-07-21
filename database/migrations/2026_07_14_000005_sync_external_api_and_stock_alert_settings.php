<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        if(!Schema::hasTable("companies") || !Schema::hasTable("company_settings")) {

            return;

        }

        foreach(DB::table("companies")->pluck("id") as $companyId) {

            $this->syncSetting(
                (int) $companyId,
                "inventory",
                "stock_alert_email_enabled",
                "false",
                "Activa el envío de correo cuando un producto cae por debajo o iguala su stock mínimo en un almacén. Solo se notifica al abrir una nueva alerta para evitar correos repetidos.",
                "boolean"
            );

            $this->syncSetting(
                (int) $companyId,
                "inventory",
                "stock_alert_email_to",
                null,
                "Correo destino para alertas de stock mínimo. Si queda vacío, se usa el correo registrado de la empresa cuando exista.",
                "string"
            );

            $this->syncSetting(
                (int) $companyId,
                "external_api",
                "document_lookup_monthly_warning_threshold",
                "80",
                "Cantidad mensual de consultas externas de DNI/RUC desde la cual el sistema devuelve una advertencia para revisar consumo y costos del proveedor.",
                "integer"
            );

            $this->syncSetting(
                (int) $companyId,
                "reports",
                "sale_share_ttl_minutes",
                "4320",
                "Tiempo de vigencia, en minutos, de los enlaces firmados para compartir o imprimir comprobantes de venta fuera de la sesión autenticada.",
                "integer"
            );

            foreach([
                ["decimal_precision", "3", "Cantidad de decimales permitidos y usados para redondear montos, cantidades, costos, tributos, pagos e inventario en validaciones y formularios.", "integer"],
                ["default_min_value", "0", "Valor minimo operativo usado por defecto en validaciones numericas cuando el campo no define una regla mas especifica.", "decimal"],
                ["default_max_value", "999999999999.999", "Valor maximo operativo usado por defecto en validaciones numericas de cantidades, precios, totales, pagos, costos y saldos.", "decimal"],
                ["max_file_size_kb", "4096", "Tamanio maximo por defecto, en KB, para archivos validados desde formularios de la empresa.", "integer"]
            ] as [$key, $value, $description, $valueType]) {

                $this->syncSetting(
                    (int) $companyId,
                    "numeric_validation",
                    $key,
                    $value,
                    $description,
                    $valueType
                );

            }

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        if(!Schema::hasTable("company_settings")) {

            return;

        }

        DB::table("company_settings")
            ->where(function($query) {
                $query->where(function($query) {
                    $query->where("group", "inventory")
                        ->whereIn("key", ["stock_alert_email_enabled", "stock_alert_email_to"]);
                })->orWhere(function($query) {
                    $query->where("group", "external_api")
                        ->where("key", "document_lookup_monthly_warning_threshold");
                })->orWhere(function($query) {
                    $query->where("group", "reports")
                        ->where("key", "sale_share_ttl_minutes");
                })->orWhere(function($query) {
                    $query->where("group", "numeric_validation")
                        ->whereIn("key", [
                            "decimal_precision",
                            "default_min_value",
                            "default_max_value",
                            "max_file_size_kb"
                        ]);
                });
            })
            ->delete();

    }

    private function syncSetting(
        int $companyId,
        string $group,
        string $key,
        ?string $value,
        string $description,
        string $valueType
    ): void {

        DB::table("company_settings")->updateOrInsert(
            [
                "company_id" => $companyId,
                "group" => $group,
                "key" => $key
            ],
            [
                "company_id" => $companyId,
                "group" => $group,
                "key" => $key,
                "value" => $value,
                "description" => $description,
                "value_type" => $valueType,
                "status" => "active"
            ]
        );

    }

};
