<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\System\Utilities;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class EnableCompanyDefaults extends Command {

    protected $signature = 'company:enable {company_id : ID de la empresa} {--skip-modules : No habilita módulos en el menú/perfiles}';

    protected $description = 'Habilita datos base, configuración operativa y accesos iniciales para una empresa.';

    public function handle(): int {

        $companyId = (int) $this->argument('company_id');
        $company = DB::table('companies')->where('id', $companyId)->first();

        if(!$company) {
            $this->error("No existe una empresa con ID {$companyId}.");
            return self::FAILURE;
        }

        DB::transaction(function() use ($companyId): void {
            $this->seedIdentityDocumentTypes($companyId);
            $this->seedDocumentTypes($companyId);
            $this->seedCurrencies($companyId);
            $this->ensureCompanyMasterReferences($companyId);
            $this->seedSettings($companyId);
            $this->seedTaxes($companyId);
            $this->seedPaymentMethods($companyId);

            if(!$this->option('skip-modules')) {
                $this->enableModules($companyId);
                $this->ensureAdminRole($companyId);
            }
        });

        $this->info("Empresa {$companyId} habilitada con configuración base.");
        return self::SUCCESS;

    }

    private function seedIdentityDocumentTypes(int $companyId): void {

        $records = [
            ['code' => 'doc.trib.no.dom.sin.ruc', 'name' => 'Doc. trib. no dom. sin RUC', 'is_searchable' => false, 'min_length' => 15, 'max_length' => 15],
            ['code' => 'dni', 'name' => 'DNI', 'is_searchable' => true, 'min_length' => 8, 'max_length' => 8],
            ['code' => 'ce', 'name' => 'CE', 'is_searchable' => false, 'min_length' => 12, 'max_length' => 12],
            ['code' => 'ruc', 'name' => 'RUC', 'is_searchable' => true, 'min_length' => 11, 'max_length' => 11],
            ['code' => 'pasaporte', 'name' => 'Pasaporte', 'is_searchable' => false, 'min_length' => 8, 'max_length' => 8]
        ];

        foreach($records as $record) {
            DB::table('identity_document_types')->updateOrInsert(
                ['company_id' => $companyId, 'code' => $record['code']],
                $record + ['company_id' => $companyId, 'status' => 'active']
            );
        }

    }

    private function seedDocumentTypes(int $companyId): void {

        foreach([
            ['code' => 'BV', 'name' => 'BOLETA DE VENTA'],
            ['code' => 'FA', 'name' => 'FACTURA']
        ] as $record) {
            DB::table('document_types')->updateOrInsert(
                ['company_id' => $companyId, 'code' => $record['code']],
                $record + ['company_id' => $companyId, 'status' => 'active']
            );
        }

    }

    private function seedCurrencies(int $companyId): void {

        DB::table('currencies')->updateOrInsert(
            ['company_id' => $companyId, 'code' => 'PEN'],
            [
                'company_id' => $companyId,
                'code' => 'PEN',
                'sign' => 'S/',
                'singular_name' => 'SOL',
                'plural_name' => 'SOLES',
                'status' => 'active'
            ]
        );

    }

    private function ensureCompanyMasterReferences(int $companyId): void {

        $identityDocumentTypeId = DB::table('identity_document_types')
            ->where('company_id', $companyId)
            ->where('code', 'ruc')
            ->value('id');

        $currencyId = DB::table('currencies')
            ->where('company_id', $companyId)
            ->where('code', 'PEN')
            ->value('id');

        DB::table('companies')
            ->where('id', $companyId)
            ->update([
                'identity_document_type_id' => $identityDocumentTypeId,
                'currency_id' => $currencyId
            ]);

    }

    private function seedSettings(int $companyId): void {

        $settings = [
            ['group' => 'internal_code_prefixes', 'key' => 'product', 'value' => 'PRO', 'description' => 'Prefijo usado para generar códigos internos de productos. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'service', 'value' => 'SER', 'description' => 'Prefijo usado para generar códigos internos de servicios. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'subscription', 'value' => 'MEM', 'description' => 'Prefijo usado para generar códigos internos de membresías. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'brand', 'value' => 'MAR', 'description' => 'Prefijo usado para generar códigos internos de marcas. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'category', 'value' => 'CAT', 'description' => 'Prefijo usado para generar códigos internos de categorías. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'branch', 'value' => 'SUC', 'description' => 'Prefijo usado para generar códigos internos de sucursales. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'asset', 'value' => 'ACT', 'description' => 'Prefijo usado para generar códigos internos de activos. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'internal_code_prefixes', 'key' => 'recipe', 'value' => 'REC', 'description' => 'Prefijo sugerido para identificar recetas, platillos y configuraciones operativas de cocina. Si el valor queda vacío, el código se guarda sin prefijo.', 'value_type' => 'string'],
            ['group' => 'inventory', 'key' => 'allow_negative_stock_on_sale', 'value' => 'false', 'description' => 'Define si una venta normal o POS/caja puede dejar productos con stock negativo. Por defecto es false: si la salida supera el stock disponible, la venta se bloquea antes de confirmar.', 'value_type' => 'boolean'],
            ['group' => 'inventory', 'key' => 'restore_stock_on_sale_cancellation', 'value' => 'false', 'description' => 'Define si al anular una venta se devuelven automáticamente los productos al almacén original. Por defecto es false: la devolución física debe registrarse desde Inventario si corresponde.', 'value_type' => 'boolean'],
            ['group' => 'inventory', 'key' => 'valuation_method', 'value' => 'weighted_average', 'description' => 'Método usado para valorizar inventario y kardex. El valor inicial weighted_average calcula costo promedio ponderado sobre entradas y saldos.', 'value_type' => 'string']
        ];

        foreach($settings as $setting) {
            DB::table('company_settings')->updateOrInsert(
                ['company_id' => $companyId, 'group' => $setting['group'], 'key' => $setting['key']],
                $setting + ['company_id' => $companyId]
            );
        }

    }

    private function seedTaxes(int $companyId): void {

        $taxes = [
            ['code' => 'SALE-IGV', 'name' => 'IGV', 'description' => 'Impuesto General a las Ventas del Perú aplicado a ventas. Si el item incluye IGV, se calcula como tributo contenido; si no lo incluye, se suma al total.', 'scope' => 'sale', 'calculation_type' => 'percentage', 'rate' => 18, 'min_apply_quantity' => null, 'max_apply_quantity' => null, 'operation_type' => 'addition', 'is_required' => true, 'is_default' => true],
            ['code' => 'SALE-ICBP', 'name' => 'ICBP', 'description' => 'Impuesto al Consumo de Bolsas Plásticas aplicado a ventas cuando corresponde. Es opcional porque no todas las ventas incluyen bolsa gravada.', 'scope' => 'sale', 'calculation_type' => 'fixed', 'rate' => 0.5, 'min_apply_quantity' => 0, 'max_apply_quantity' => null, 'operation_type' => 'addition', 'is_required' => false, 'is_default' => false],
            ['code' => 'PURCHASE-IGV', 'name' => 'IGV', 'description' => 'Impuesto General a las Ventas del Perú aplicado a compras. Se calcula sobre la base de compra registrada.', 'scope' => 'purchase', 'calculation_type' => 'percentage', 'rate' => 18, 'min_apply_quantity' => null, 'max_apply_quantity' => null, 'operation_type' => 'addition', 'is_required' => true, 'is_default' => true],
            ['code' => 'PURCHASE-ICBP', 'name' => 'ICBP', 'description' => 'Impuesto al Consumo de Bolsas Plásticas aplicado a compras cuando corresponde. Es opcional porque no todas las compras incluyen bolsa gravada.', 'scope' => 'purchase', 'calculation_type' => 'fixed', 'rate' => 0.5, 'min_apply_quantity' => 0, 'max_apply_quantity' => null, 'operation_type' => 'addition', 'is_required' => false, 'is_default' => false]
        ];

        foreach($taxes as $tax) {
            DB::table('taxes')->updateOrInsert(
                ['company_id' => $companyId, 'code' => $tax['code']],
                $tax + ['company_id' => $companyId, 'status' => 'active']
            );
        }

    }

    private function seedPaymentMethods(int $companyId): void {

        $methods = [
            ['code' => 'CASH', 'sunat_code' => '008', 'name' => 'Efectivo', 'image_path' => 'System/assets/img/payment-methods/cash.svg', 'scope' => 'both', 'requires_reference' => false, 'is_default' => true],
            ['code' => 'BANK_DEPOSIT', 'sunat_code' => '001', 'name' => 'Depósito en cuenta', 'image_path' => 'System/assets/img/payment-methods/bank-deposit.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'BANK_TRANSFER', 'sunat_code' => '003', 'name' => 'Transferencia de fondos', 'image_path' => 'System/assets/img/payment-methods/bank-transfer.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'DEBIT_CARD', 'sunat_code' => '005', 'name' => 'Tarjeta de débito', 'image_path' => 'System/assets/img/payment-methods/debit-card.svg', 'scope' => 'sale', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'CREDIT_CARD', 'sunat_code' => '006', 'name' => 'Tarjeta de crédito', 'image_path' => 'System/assets/img/payment-methods/credit-card.svg', 'scope' => 'sale', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'CHECK', 'sunat_code' => '007', 'name' => 'Cheque no negociable', 'image_path' => 'System/assets/img/payment-methods/check.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'DIGITAL_WALLET', 'sunat_code' => null, 'name' => 'Billetera digital', 'image_path' => 'System/assets/img/payment-methods/digital-wallet.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'YAPE', 'sunat_code' => null, 'name' => 'Yape', 'image_path' => 'System/assets/img/payment-methods/yape.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false],
            ['code' => 'PLIN', 'sunat_code' => null, 'name' => 'Plin', 'image_path' => 'System/assets/img/payment-methods/plin.svg', 'scope' => 'both', 'requires_reference' => true, 'is_default' => false]
        ];

        foreach($methods as $method) {
            DB::table('payment_methods')->updateOrInsert(
                ['company_id' => $companyId, 'code' => $method['code']],
                $method + ['company_id' => $companyId, 'status' => 'active']
            );
        }

    }

    private function enableModules(int $companyId): void {

        $subSections = DB::table('sub_sections')
            ->join('sections', 'sections.id', '=', 'sub_sections.section_id')
            ->select('sub_sections.id', 'sub_sections.order as sub_section_order', 'sections.order as section_order')
            ->get();

        foreach($subSections as $subSection) {
            DB::table('companies_sub_sections')->updateOrInsert(
                ['company_id' => $companyId, 'sub_section_id' => $subSection->id],
                [
                    'company_id' => $companyId,
                    'sub_section_id' => $subSection->id,
                    'section_order' => $subSection->section_order,
                    'sub_section_order' => $subSection->sub_section_order,
                    'status' => 'active'
                ]
            );
        }

    }

    private function ensureAdminRole(int $companyId): void {

        $existingRoleId = DB::table('roles')
            ->where('company_id', $companyId)
            ->where('is_full_access', true)
            ->value('id');

        if(!$existingRoleId) {
            DB::table('roles')->insert([
                'company_id' => $companyId,
                'slug' => Utilities::generateCode(),
                'name' => 'Administrador',
                'is_full_access' => true,
                'status' => 'active'
            ]);

            $existingRoleId = DB::table('roles')
                ->where('company_id', $companyId)
                ->where('is_full_access', true)
                ->value('id');
        }

        $subSectionIds = DB::table('sub_sections')->pluck('id');
        foreach($subSectionIds as $subSectionId) {
            DB::table('role_sub_sections')->updateOrInsert(
                ['company_id' => $companyId, 'role_id' => $existingRoleId, 'sub_section_id' => $subSectionId],
                ['company_id' => $companyId, 'role_id' => $existingRoleId, 'sub_section_id' => $subSectionId, 'status' => 'active']
            );
        }

    }

}