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
