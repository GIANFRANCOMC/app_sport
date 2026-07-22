<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Services\System\Organizations\Companies\CompanySectionService;

return new class extends Migration {

    private const SECTIONS = [
        ["id" => 1, "slug" => "sc_home", "name" => "home", "order" => 1, "dom_id" => "menu-parent-home", "dom_label" => "Inicio", "dom_icon" => "fa fa-home", "has_sub_menu" => false, "status" => "active"],
        ["id" => 2, "slug" => "sc_dashboard", "name" => "dashboard", "order" => 2, "dom_id" => "menu-parent-dashboard", "dom_label" => "Dashboard", "dom_icon" => "fa-solid fa-gauge", "has_sub_menu" => false, "status" => "active"],
        ["id" => 14, "slug" => "sc_pos", "name" => "pos", "order" => 3, "dom_id" => "menu-parent-pos", "dom_label" => "POS", "dom_icon" => "fa-solid fa-store", "has_sub_menu" => true, "status" => "active"],
        ["id" => 3, "slug" => "sc_sales", "name" => "sales", "order" => 4, "dom_id" => "menu-parent-sales", "dom_label" => "Ventas", "dom_icon" => "fa-solid fa-cash-register", "has_sub_menu" => true, "status" => "active"],
        ["id" => 16, "slug" => "sc_quotations", "name" => "quotations", "order" => 5, "dom_id" => "menu-parent-quotations", "dom_label" => "Cotizaciones", "dom_icon" => "fa-solid fa-file-signature", "has_sub_menu" => true, "status" => "active"],
        ["id" => 10, "slug" => "sc_cash", "name" => "cash", "order" => 6, "dom_id" => "menu-parent-cash", "dom_label" => "Cajas", "dom_icon" => "fa-solid fa-vault", "has_sub_menu" => true, "status" => "active"],
        ["id" => 9, "slug" => "sc_purchases", "name" => "purchases", "order" => 7, "dom_id" => "menu-parent-purchases", "dom_label" => "Compras", "dom_icon" => "fa-solid fa-cart-flatbed", "has_sub_menu" => true, "status" => "active"],
        ["id" => 15, "slug" => "sc_customer_attention", "name" => "customer-attention", "order" => 8, "dom_id" => "menu-parent-customer-attention", "dom_label" => "Atención al cliente", "dom_icon" => "fa-solid fa-headset", "has_sub_menu" => true, "status" => "active"],
        ["id" => 4, "slug" => "sc_customers", "name" => "customers", "order" => 9, "dom_id" => "menu-parent-customers", "dom_label" => "Gestión de clientes", "dom_icon" => "fa fa-user", "has_sub_menu" => true, "status" => "active"],
        ["id" => 12, "slug" => "sc_staff", "name" => "staff", "order" => 10, "dom_id" => "menu-parent-staff", "dom_label" => "Gestión de colaboradores", "dom_icon" => "fa-solid fa-users-gear", "has_sub_menu" => true, "status" => "active"],
        ["id" => 5, "slug" => "sc_items", "name" => "items", "order" => 11, "dom_id" => "menu-parent-items", "dom_label" => "Catálogo comercial", "dom_icon" => "fa fa-book", "has_sub_menu" => true, "status" => "active"],
        ["id" => 11, "slug" => "sc_inventory", "name" => "inventory", "order" => 12, "dom_id" => "menu-parent-inventory", "dom_label" => "Inventario", "dom_icon" => "fa-solid fa-boxes-stacked", "has_sub_menu" => true, "status" => "active"],
        ["id" => 13, "slug" => "sc_restaurant_services", "name" => "restaurant-services", "order" => 13, "dom_id" => "menu-parent-restaurant-services", "dom_label" => "Restaurante y servicios", "dom_icon" => "fa-solid fa-bell-concierge", "has_sub_menu" => true, "status" => "active"],
        ["id" => 6, "slug" => "sc_infrastructure", "name" => "infrastructure", "order" => 14, "dom_id" => "menu-parent-infrastructure", "dom_label" => "Infraestructura", "dom_icon" => "fa-solid fa-industry", "has_sub_menu" => true, "status" => "active"],
        ["id" => 7, "slug" => "sc_configuration", "name" => "configuration", "order" => 15, "dom_id" => "menu-parent-configuration", "dom_label" => "Configuración", "dom_icon" => "fa fa-gear", "has_sub_menu" => true, "status" => "active"],
        ["id" => 8, "slug" => "sc_reports", "name" => "reports", "order" => 16, "dom_id" => "menu-parent-reports", "dom_label" => "Reportes", "dom_icon" => "fa fa-print", "has_sub_menu" => false, "status" => "active"]
    ];

    private const SUB_SECTIONS = [
        10 => ["section_id" => 1, "slug" => "sc_home", "name" => "home", "description" => "Organiza y abre tus accesos favoritos del sistema.", "order" => 1, "dom_id" => "menu-home", "dom_label" => "Inicio", "dom_route" => "home.index"],
        20 => ["section_id" => 2, "slug" => "sc_dashboard", "name" => "dashboard", "description" => "Consulta indicadores y el estado general de la operación.", "order" => 1, "dom_id" => "menu-dashboard", "dom_label" => "Dashboard", "dom_route" => "dashboard.index"],
        32 => ["section_id" => 14, "slug" => "sc_sales-pos", "name" => "sales-pos", "description" => "Venta rápida para mostrador, vinculada a almacén y caja activa.", "order" => 1, "dom_id" => "menu-sales-pos", "dom_label" => "Venta POS", "dom_route" => "sales.pos"],
        105 => ["section_id" => 14, "slug" => "sc_restaurant-pos", "name" => "restaurant-pos", "description" => "Gestiona mesas, pedidos en curso y su posterior cobro en POS.", "order" => 2, "dom_id" => "menu-restaurant-pos", "dom_label" => "Restaurante POS", "dom_route" => "restaurant_pos.index"],
        31 => ["section_id" => 3, "slug" => "sc_sales-create", "name" => "sales-create", "description" => "Registra una nueva venta de productos, servicios o membresías.", "order" => 1, "dom_id" => "menu-sales-create", "dom_label" => "Nuevo", "dom_route" => "sales.create"],
        30 => ["section_id" => 3, "slug" => "sc_sales-list", "name" => "sales-list", "description" => "Revisa las ventas registradas y consulta sus detalles.", "order" => 2, "dom_id" => "menu-sales-list", "dom_label" => "Listado", "dom_route" => "sales.index"],
        34 => ["section_id" => 16, "slug" => "sc_quotations-create", "name" => "quotations-create", "description" => "Registra una propuesta comercial con detalle, tributos y vigencia.", "order" => 1, "dom_id" => "menu-quotations-create", "dom_label" => "Nuevo", "dom_route" => "quotations.create"],
        33 => ["section_id" => 16, "slug" => "sc_quotations-list", "name" => "quotations-list", "description" => "Consulta propuestas comerciales y conviértelas en ventas recalculando precios vigentes.", "order" => 2, "dom_id" => "menu-quotations-list", "dom_label" => "Listado", "dom_route" => "quotations.index"],
        100 => ["section_id" => 10, "slug" => "sc_cash-registers", "name" => "cash-registers", "description" => "Gestiona cajas configuradas por sucursal y su estado operativo.", "order" => 1, "dom_id" => "menu-cash-registers", "dom_label" => "Cajas", "dom_route" => "cash_registers.registers.index"],
        101 => ["section_id" => 10, "slug" => "sc_cash-sessions", "name" => "cash-sessions", "description" => "Consulta aperturas, cierres, arqueos y diferencias por caja.", "order" => 2, "dom_id" => "menu-cash-sessions", "dom_label" => "Aperturas y cierres", "dom_route" => "cash_registers.sessions.index"],
        102 => ["section_id" => 10, "slug" => "sc_cash-movements", "name" => "cash-movements", "description" => "Registra y consulta entradas, salidas y ajustes manuales de dinero.", "order" => 3, "dom_id" => "menu-cash-movements", "dom_label" => "Movimientos", "dom_route" => "cash_registers.movements.index"],
        103 => ["section_id" => 10, "slug" => "sc_cash-summary", "name" => "cash-summary", "description" => "Revisa resumen de caja por métodos de pago, esperado, contado y diferencia.", "order" => 4, "dom_id" => "menu-cash-summary", "dom_label" => "Resumen", "dom_route" => "cash_registers.summary.index"],
        109 => ["section_id" => 10, "slug" => "sc_misc-expenses", "name" => "misc-expenses", "description" => "Registra gastos no ligados a compras de inventario, con responsable y contexto financiero.", "order" => 5, "dom_id" => "menu-misc-expenses", "dom_label" => "Gastos varios", "dom_route" => "misc_expenses.index"],
        92 => ["section_id" => 9, "slug" => "sc_purchases-new", "name" => "purchases-new", "description" => "Registra una compra nueva con productos, costos, tributos, pagos y recepción.", "order" => 1, "dom_id" => "menu-purchases-new", "dom_label" => "Nuevo", "dom_route" => "purchases.create"],
        90 => ["section_id" => 9, "slug" => "sc_purchases-list", "name" => "purchases-list", "description" => "Consulta compras registradas, recepción, estado de pago y trazabilidad.", "order" => 2, "dom_id" => "menu-purchases-list", "dom_label" => "Listado", "dom_route" => "purchases.index"],
        91 => ["section_id" => 9, "slug" => "sc_purchases-suppliers", "name" => "purchases-suppliers", "description" => "Administra proveedores y sus datos comerciales.", "order" => 3, "dom_id" => "menu-purchases-suppliers", "dom_label" => "Proveedores", "dom_route" => "suppliers.index"],
        106 => ["section_id" => 15, "slug" => "sc_service-sessions", "name" => "service-sessions", "description" => "Inicia, asigna y finaliza servicios midiendo su tiempo real.", "order" => 1, "dom_id" => "menu-service-sessions", "dom_label" => "Servicios en curso", "dom_route" => "service_sessions.index"],
        45 => ["section_id" => 15, "slug" => "sc_customers-book_complaints", "name" => "customers-book_complaints", "description" => "Atiende reclamos, quejas y sugerencias recibidas.", "order" => 2, "dom_id" => "menu-customers-book_complaints", "dom_label" => "Libro de reclamaciones", "dom_route" => "book_complaints.index"],
        44 => ["section_id" => 15, "slug" => "sc_customers-notifications", "name" => "customers-notifications", "description" => "Gestiona comunicaciones y avisos dirigidos a clientes.", "order" => 3, "dom_id" => "menu-customers-notifications", "dom_label" => "Notificaciones", "dom_route" => "tracking_notifications.index"],
        43 => ["section_id" => 15, "slug" => "sc_customers-attendances", "name" => "customers-attendances", "description" => "Registra y consulta ingresos y salidas de clientes por documento.", "order" => 4, "dom_id" => "menu-customers-attendances", "dom_label" => "Asistencias por documento", "dom_route" => "tracking_attendances.index"],
        40 => ["section_id" => 4, "slug" => "sc_customers", "name" => "customers", "description" => "Administra los datos y el estado de los clientes.", "order" => 1, "dom_id" => "menu-customers", "dom_label" => "Clientes", "dom_route" => "customers.index"],
        41 => ["section_id" => 4, "slug" => "sc_customers-history", "name" => "customers-history", "description" => "Consulta la actividad y los movimientos de cada cliente.", "order" => 2, "dom_id" => "menu-customers-history", "dom_label" => "Historial", "dom_route" => "tracking_customers.index"],
        42 => ["section_id" => 4, "slug" => "sc_customers-subscriptions", "name" => "customers-subscriptions", "description" => "Controla vigencias, renovaciones y estados de membresías.", "order" => 3, "dom_id" => "menu-customers-subscriptions", "dom_label" => "Membresías", "dom_route" => "tracking_subscriptions.index"],
        71 => ["section_id" => 12, "slug" => "sc_configuration-users", "name" => "configuration-users", "description" => "Administra colaboradores internos, roles y accesos.", "order" => 1, "dom_id" => "menu-configuration-users", "dom_label" => "Colaboradores", "dom_route" => "users.index"],
        104 => ["section_id" => 12, "slug" => "sc_user-attendances", "name" => "user-attendances", "description" => "Controla ingresos, salidas y horas trabajadas por colaborador.", "order" => 2, "dom_id" => "menu-user-attendances", "dom_label" => "Asistencia del personal", "dom_route" => "user_attendances.index"],
        50 => ["section_id" => 5, "slug" => "sc_items-products", "name" => "items-products", "description" => "Administra productos, precios y disponibilidad comercial.", "order" => 1, "dom_id" => "menu-items-products", "dom_label" => "Productos", "dom_route" => "products.index"],
        51 => ["section_id" => 5, "slug" => "sc_items-services", "name" => "items-services", "description" => "Configura los servicios ofrecidos por la empresa.", "order" => 2, "dom_id" => "menu-items-services", "dom_label" => "Servicios", "dom_route" => "services.index"],
        52 => ["section_id" => 5, "slug" => "sc_items-subscriptions", "name" => "items-subscriptions", "description" => "Define planes de membresía, duración y precio de venta.", "order" => 3, "dom_id" => "menu-items-subscriptions", "dom_label" => "Membresías", "dom_route" => "subscriptions.index"],
        53 => ["section_id" => 5, "slug" => "sc_items-categories", "name" => "items-categories", "description" => "Organiza productos y servicios mediante categorías.", "order" => 4, "dom_id" => "menu-items-categories", "dom_label" => "Categorías", "dom_route" => "categories.index"],
        55 => ["section_id" => 5, "slug" => "sc_items-brands", "name" => "items-brands", "description" => "Administra las marcas utilizadas para identificar y agrupar productos.", "order" => 5, "dom_id" => "menu-items-brands", "dom_label" => "Marcas", "dom_route" => "brands.index"],
        59 => ["section_id" => 5, "slug" => "sc_items-recipes", "name" => "items-recipes", "description" => "Configura recetas, platillos, toppings, sabores e insumos para negocios de comida.", "order" => 6, "dom_id" => "menu-items-recipes", "dom_label" => "Recetas y platillos", "dom_route" => "recipes.index"],
        54 => ["section_id" => 11, "slug" => "sc_inventory-stock", "name" => "inventory-stock", "description" => "Consulta existencias actuales, mínimos y alertas por almacén.", "order" => 1, "dom_id" => "menu-inventory-stock", "dom_label" => "Control de stock", "dom_route" => "stocks_management.stock.index"],
        56 => ["section_id" => 11, "slug" => "sc_inventory-kardex", "name" => "inventory-kardex", "description" => "Consulta la trazabilidad de entradas, salidas, correcciones y saldos resultantes.", "order" => 2, "dom_id" => "menu-inventory-kardex", "dom_label" => "Kardex", "dom_route" => "stocks_management.kardex.index"],
        57 => ["section_id" => 11, "slug" => "sc_inventory-transfers", "name" => "inventory-transfers", "description" => "Registra y consulta traslados de productos entre almacenes.", "order" => 3, "dom_id" => "menu-inventory-transfers", "dom_label" => "Traslados", "dom_route" => "stocks_management.transfers.index"],
        107 => ["section_id" => 11, "slug" => "sc_inventory-guides", "name" => "inventory-guides", "description" => "Consulta guías numeradas de entrada y salida con estado, almacén y detalle.", "order" => 4, "dom_id" => "menu-inventory-guides", "dom_label" => "Guías", "dom_route" => "stocks_management.guides.index"],
        58 => ["section_id" => 11, "slug" => "sc_inventory-valued", "name" => "inventory-valued", "description" => "Consulta el kardex valorizado con costo unitario, valor de movimiento y saldo valorizado.", "order" => 5, "dom_id" => "menu-inventory-valued", "dom_label" => "Kardex valorizado", "dom_route" => "stocks_management.valued.index"],
        60 => ["section_id" => 6, "slug" => "sc_infrastructure-branches", "name" => "infrastructure-branches", "description" => "Administra sedes, datos de contacto y capacidad.", "order" => 1, "dom_id" => "menu-infrastructure-branches", "dom_label" => "Sucursales", "dom_route" => "branches.index"],
        61 => ["section_id" => 6, "slug" => "sc_infrastructure-assets", "name" => "infrastructure-assets", "description" => "Mantén el catálogo de equipos y bienes de la empresa.", "order" => 2, "dom_id" => "menu-infrastructure-assets", "dom_label" => "Activos", "dom_route" => "assets.index"],
        62 => ["section_id" => 6, "slug" => "sc_infrastructure-assets_management", "name" => "infrastructure-assets_management", "description" => "Asigna, traslada y controla activos por sede o usuario.", "order" => 3, "dom_id" => "menu-infrastructure-assets_management", "dom_label" => "Gestión de activos", "dom_route" => "assets_management.index"],
        63 => ["section_id" => 6, "slug" => "sc_infrastructure-biometric_devices", "name" => "infrastructure-biometric_devices", "description" => "Configura los dispositivos usados para control biométrico.", "order" => 4, "dom_id" => "menu-infrastructure-biometric_devices", "dom_label" => "Dispositivos biométricos", "dom_route" => "biometric_devices.index"],
        70 => ["section_id" => 7, "slug" => "sc_configuration-my_company", "name" => "configuration-my_company", "description" => "Actualiza la identidad y los datos generales de la empresa.", "order" => 1, "dom_id" => "menu-configuration-my_company", "dom_label" => "Mi empresa", "dom_route" => "companies.index"],
        72 => ["section_id" => 7, "slug" => "sc_configuration-roles", "name" => "configuration-roles", "description" => "Define perfiles de acceso y módulos disponibles para cada colaborador.", "order" => 2, "dom_id" => "menu-configuration-roles", "dom_label" => "Perfiles de acceso", "dom_route" => "roles.index"],
        73 => ["section_id" => 7, "slug" => "sc_configuration-master_data", "name" => "configuration-master_data", "description" => "Administra configuraciones, tributos, métodos de pago y maestros internos por empresa.", "order" => 3, "dom_id" => "menu-configuration-master_data", "dom_label" => "Maestros internos", "dom_route" => "master_data.index"],
        74 => ["section_id" => 7, "slug" => "sc_configuration-business-profile", "name" => "configuration-business-profile", "description" => "Configura el rubro de la empresa y el set base de módulos sugeridos.", "order" => 4, "dom_id" => "menu-configuration-business-profile", "dom_label" => "Rubro y módulos", "dom_route" => "business_profile.index"],
        80 => ["section_id" => 8, "slug" => "sc_reports", "name" => "reports", "description" => "Genera consultas y reportes para análisis operativo.", "order" => 1, "dom_id" => "menu-reports", "dom_label" => "Reportes", "dom_route" => "reports.index"]
    ];

    private const SECTION_ORDERS = [
        10 => 1, 20 => 2, 32 => 3, 105 => 3, 31 => 4, 30 => 4,
        34 => 5, 33 => 5,
        100 => 6, 101 => 6, 102 => 6, 103 => 6, 109 => 6,
        92 => 7, 90 => 7, 91 => 7,
        106 => 8, 45 => 8, 44 => 8, 43 => 8,
        40 => 9, 41 => 9, 42 => 9,
        71 => 10, 104 => 10,
        50 => 11, 51 => 11, 52 => 11, 53 => 11, 55 => 11, 59 => 11,
        54 => 12, 56 => 12, 57 => 12, 107 => 12, 58 => 12,
        60 => 14, 61 => 14, 62 => 14, 63 => 14,
        70 => 15, 72 => 15, 73 => 15, 74 => 15,
        80 => 16
    ];

    public function up(): void {

        if(!Schema::hasTable("sections") || !Schema::hasTable("sub_sections")) {

            return;

        }

        $this->syncSections();
        $this->syncSubSections();
        $this->syncCompanySubSections();
        $this->syncBusinessIndustryModuleSets();
        $this->clearMenuCache();

    }

    public function down(): void {

        if(!Schema::hasTable("sections") || !Schema::hasTable("sub_sections")) {

            return;

        }

        DB::table("sub_sections")->where("id", 32)->update(["section_id" => 3, "order" => 1]);
        DB::table("sub_sections")->where("id", 105)->update(["section_id" => 13, "order" => 1]);
        DB::table("sub_sections")->where("id", 106)->update(["section_id" => 13, "order" => 2]);
        DB::table("sub_sections")->where("id", 43)->update(["section_id" => 4, "order" => 4, "dom_label" => "Asistencias por documento"]);
        DB::table("sub_sections")->where("id", 44)->update(["section_id" => 4, "order" => 5]);
        DB::table("sub_sections")->where("id", 45)->update(["section_id" => 4, "order" => 6, "dom_label" => "Libro de reclamaciones y sugerencias"]);
        DB::table("sub_sections")->where("id", 33)->update([
            "section_id" => 3,
            "slug" => "sc_sales-quotations",
            "name" => "sales-quotations",
            "order" => 3,
            "dom_id" => "menu-sales-quotations",
            "dom_label" => "Cotizaciones",
            "dom_route" => "quotations.index"
        ]);
        DB::table("sub_sections")->whereIn("id", [34, 74, 109])->delete();
        DB::table("sections")->whereIn("id", [14, 15, 16])->delete();

        $this->clearMenuCache();

    }

    private function syncSections(): void {

        foreach(self::SECTIONS as $section) {

            DB::table("sections")->updateOrInsert(["id" => $section["id"]], $section);

        }

    }

    private function syncSubSections(): void {

        foreach(self::SUB_SECTIONS as $id => $subSection) {

            DB::table("sub_sections")->updateOrInsert(
                ["id" => $id],
                $subSection + ["status" => "active"]
            );

        }

    }

    private function syncCompanySubSections(): void {

        if(!Schema::hasTable("companies") || !Schema::hasTable("companies_sub_sections")) {

            return;

        }

        DB::table("companies")
            ->pluck("id")
            ->each(function($companyId) {

                foreach(self::SUB_SECTIONS as $subSectionId => $subSection) {

                    DB::table("companies_sub_sections")->updateOrInsert(
                        ["company_id" => $companyId, "sub_section_id" => $subSectionId],
                        [
                            "section_order" => self::SECTION_ORDERS[$subSectionId] ?? $subSection["section_id"],
                            "sub_section_order" => $subSection["order"],
                            "status" => "active"
                        ]
                    );

                }

            });

    }

    private function syncBusinessIndustryModuleSets(): void {

        if(!Schema::hasTable("business_industries") || !Schema::hasTable("business_industry_module_sets")) {

            return;

        }

        $disabledByIndustry = [
            "gym" => [59, 105],
            "restaurant" => [42, 52, 43],
            "retail" => [42, 52, 43, 59, 105, 106]
        ];

        DB::table("business_industries")
            ->select(["id", "company_id", "slug", "name"])
            ->where("status", "active")
            ->orderBy("company_id")
            ->get()
            ->each(function($industry) use($disabledByIndustry) {

                $disabledModules = $disabledByIndustry[$industry->slug] ?? [];

                foreach(array_keys(self::SUB_SECTIONS) as $subSectionId) {

                    $isEnabled = !in_array($subSectionId, $disabledModules, true);

                    DB::table("business_industry_module_sets")->updateOrInsert(
                        [
                            "company_id" => $industry->company_id,
                            "business_industry_id" => $industry->id,
                            "sub_section_id" => $subSectionId
                        ],
                        [
                            "is_enabled_by_default" => $isEnabled,
                            "reason" => $isEnabled
                                ? "Módulo sugerido para el rubro {$industry->name}."
                                : "Módulo normalmente no usado por el rubro {$industry->name}; puede habilitarse manualmente si aplica.",
                            "status" => "active",
                            "created_at" => now(),
                            "updated_at" => now()
                        ]
                    );

                }

            });

    }

    private function clearMenuCache(): void {

        if(!Schema::hasTable("companies")) {

            return;

        }

        DB::table("companies")
            ->pluck("id")
            ->each(function($companyId) {

                $companyId = (int) $companyId;

                if(Schema::hasTable("roles")) {

                    CompanySectionService::clearCompanyCache($companyId);

                    return;

                }

                CompanySectionService::clearCache($companyId);

            });

    }

};
