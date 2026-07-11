<?php

use App\Helpers\System\Utilities;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Hash};

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {

        DB::table("companies")->insert([
            [
                "id" => 1,
                "slug" => "pagape",
                "internal_code" => Utilities::generateCode(7),
                "identity_document_type_id" => null,
                "document_number" => "999999999999999",
                "legal_name" => "BLAPOS S.A.",
                "commercial_name" => "BLAPOS",
                "currency_id" => null,
                "address" => "",
                "telephone" => "",
                "email" => ""
            ]
        ]);

        DB::table("identity_document_types")->insert([
            ["id" => 1, "company_id" => 1, "code" => "doc.trib.no.dom.sin.ruc", "name" => "Doc. trib. no dom. sin RUC", "is_searchable" => false, "min_length" => 15, "max_length" => 15],
            ["id" => 2, "company_id" => 1, "code" => "dni", "name" => "DNI", "is_searchable" => true, "min_length" => 8, "max_length" => 8],
            ["id" => 3, "company_id" => 1, "code" => "ce", "name" => "CE", "is_searchable" => false, "min_length" => 12, "max_length" => 12],
            ["id" => 4, "company_id" => 1, "code" => "ruc", "name" => "RUC", "is_searchable" => true, "min_length" => 11, "max_length" => 11],
            ["id" => 5, "company_id" => 1, "code" => "pasaporte", "name" => "Pasaporte", "is_searchable" => false, "min_length" => 8, "max_length" => 8]
        ]);

        DB::table("document_types")->insert([
            ["id" => 1, "company_id" => 1, "code" => "BV", "name" => "BOLETA DE VENTA"],
            ["id" => 2, "company_id" => 1, "code" => "FA", "name" => "FACTURA"]
        ]);

        DB::table("currencies")->insert([
            ["id" => 1, "company_id" => 1, "code" => "PEN", "sign" => "S/", "singular_name" => "SOL", "plural_name" => "SOLES"]
        ]);

        DB::table("companies")->where("id", 1)->update([
            "identity_document_type_id" => 1,
            "currency_id" => 1
        ]);
        DB::table("sections")->insert([
            ["id" => 1, "slug" => "sc_home", "name" => "home", "order" => 1, "dom_id" => "menu-parent-home", "dom_label" => "Inicio", "dom_icon" => "fa fa-home", "has_sub_menu" => false],
            ["id" => 2, "slug" => "sc_dashboard", "name" => "dashboard", "order" => 2, "dom_id" => "menu-parent-dashboard", "dom_label" => "Dashboard", "dom_icon" => "fa-solid fa-gauge", "has_sub_menu" => false],
            ["id" => 3, "slug" => "sc_sales", "name" => "sales", "order" => 4, "dom_id" => "menu-parent-sales", "dom_label" => "Ventas", "dom_icon" => "fa-solid fa-cash-register", "has_sub_menu" => true],
            ["id" => 9, "slug" => "sc_purchases", "name" => "purchases", "order" => 5, "dom_id" => "menu-parent-purchases", "dom_label" => "Compras", "dom_icon" => "fa-solid fa-cart-flatbed", "has_sub_menu" => true],
            ["id" => 4, "slug" => "sc_customers", "name" => "customers", "order" => 6, "dom_id" => "menu-parent-customers", "dom_label" => "Gestión de clientes", "dom_icon" => "fa fa-user", "has_sub_menu" => true],
            ["id" => 5, "slug" => "sc_items", "name" => "items", "order" => 7, "dom_id" => "menu-parent-items", "dom_label" => "Catálogo comercial", "dom_icon" => "fa fa-book", "has_sub_menu" => true],
            ["id" => 10, "slug" => "sc_operations", "name" => "operations", "order" => 3, "dom_id" => "menu-parent-operations", "dom_label" => "Operación", "dom_icon" => "fa-solid fa-bolt", "has_sub_menu" => true],
            ["id" => 11, "slug" => "sc_inventory", "name" => "inventory", "order" => 8, "dom_id" => "menu-parent-inventory", "dom_label" => "Inventario", "dom_icon" => "fa-solid fa-boxes-stacked", "has_sub_menu" => true],
            ["id" => 6, "slug" => "sc_infrastructure", "name" => "infrastructure", "order" => 9, "dom_id" => "menu-parent-infrastructure", "dom_label" => "Infraestructura", "dom_icon" => "fa-solid fa-industry", "has_sub_menu" => true],
            ["id" => 7, "slug" => "sc_configuration", "name" => "configuration", "order" => 10, "dom_id" => "menu-parent-configuration", "dom_label" => "Configuración", "dom_icon" => "fa fa-gear", "has_sub_menu" => true],
            ["id" => 8, "slug" => "sc_reports", "name" => "reports", "order" => 11, "dom_id" => "menu-parent-reports", "dom_label" => "Reportes", "dom_icon" => "fa fa-print", "has_sub_menu" => false]
        ]);

        DB::table("sub_sections")->insert([
            // Home
            ["id" => 10, "section_id" => 1, "slug" => "sc_home", "name" => "home", "description" => "Organiza y abre tus accesos favoritos del sistema.", "order" => 1, "dom_id" => "menu-home", "dom_label" => "Inicio", "dom_route" => "home.index"],

            // Dashboard
            ["id" => 20, "section_id" => 2, "slug" => "sc_dashboard", "name" => "dashboard", "description" => "Consulta indicadores y el estado general de la operación.", "order" => 1, "dom_id" => "menu-dashboard", "dom_label" => "Dashboard", "dom_route" => "dashboard.index"],

            // Sales
            ["id" => 30, "section_id" => 3, "slug" => "sc_sales-list", "name" => "sales-list", "description" => "Revisa las ventas registradas y consulta sus detalles.", "order" => 1, "dom_id" => "menu-sales-list", "dom_label" => "Listado", "dom_route" => "sales.index"],
            ["id" => 31, "section_id" => 3, "slug" => "sc_sales-create", "name" => "sales-create", "description" => "Registra una nueva venta de productos, servicios o membresías.", "order" => 2, "dom_id" => "menu-sales-create", "dom_label" => "Nuevo", "dom_route" => "sales.create"],

            ["id" => 32, "section_id" => 10, "slug" => "sc_sales-pos", "name" => "sales-pos", "description" => "Venta rápida para mostrador, vinculada a almacén y caja activa.", "order" => 1, "dom_id" => "menu-sales-pos", "dom_label" => "Venta POS", "dom_route" => "sales.pos"],

            // Purchases
            ["id" => 90, "section_id" => 9, "slug" => "sc_purchases-list", "name" => "purchases-list", "description" => "Consulta compras registradas, recepción, estado de pago y trazabilidad.", "order" => 1, "dom_id" => "menu-purchases-list", "dom_label" => "Listado", "dom_route" => "purchases.list.index"],
            ["id" => 92, "section_id" => 9, "slug" => "sc_purchases-new", "name" => "purchases-new", "description" => "Registra una compra nueva con productos, costos, tributos, pagos y recepción.", "order" => 2, "dom_id" => "menu-purchases-new", "dom_label" => "Nuevo", "dom_route" => "purchases.new.index"],
            ["id" => 91, "section_id" => 9, "slug" => "sc_purchases-suppliers", "name" => "purchases-suppliers", "description" => "Administra proveedores y sus datos comerciales.", "order" => 3, "dom_id" => "menu-purchases-suppliers", "dom_label" => "Proveedores", "dom_route" => "suppliers.index"],

            // Customers
            ["id" => 40, "section_id" => 4, "slug" => "sc_customers", "name" => "customers", "description" => "Administra los datos y el estado de los clientes.", "order" => 1, "dom_id" => "menu-customers", "dom_label" => "Clientes", "dom_route" => "customers.index"],
            ["id" => 41, "section_id" => 4, "slug" => "sc_customers-history", "name" => "customers-history", "description" => "Consulta la actividad y los movimientos de cada cliente.", "order" => 2, "dom_id" => "menu-customers-history", "dom_label" => "Historial", "dom_route" => "tracking_customers.index"],
            ["id" => 42, "section_id" => 4, "slug" => "sc_customers-subscriptions", "name" => "customers-subscriptions", "description" => "Controla vigencias, renovaciones y estados de membresías.", "order" => 3, "dom_id" => "menu-customers-subscriptions", "dom_label" => "Membresías", "dom_route" => "tracking_subscriptions.index"],
            ["id" => 43, "section_id" => 4, "slug" => "sc_customers-attendances", "name" => "customers-attendances", "description" => "Registra y consulta ingresos y salidas de clientes.", "order" => 4, "dom_id" => "menu-customers-attendances", "dom_label" => "Asistencias", "dom_route" => "tracking_attendances.index"],
            ["id" => 44, "section_id" => 4, "slug" => "sc_customers-notifications", "name" => "customers-notifications", "description" => "Gestiona comunicaciones y avisos dirigidos a clientes.", "order" => 5, "dom_id" => "menu-customers-notifications", "dom_label" => "Notificaciones", "dom_route" => "tracking_notifications.index"],
            ["id" => 45, "section_id" => 4, "slug" => "sc_customers-book_complaints", "name" => "customers-book_complaints", "description" => "Atiende reclamos, quejas y sugerencias recibidas.", "order" => 6, "dom_id" => "menu-customers-book_complaints", "dom_label" => "Libro de reclamaciones y sugerencias", "dom_route" => "book_complaints.index"],

            // Items
            ["id" => 50, "section_id" => 5, "slug" => "sc_items-products", "name" => "items-products", "description" => "Administra productos, precios y disponibilidad comercial.", "order" => 1, "dom_id" => "menu-items-products", "dom_label" => "Productos", "dom_route" => "products.index"],
            ["id" => 51, "section_id" => 5, "slug" => "sc_items-services", "name" => "items-services", "description" => "Configura los servicios ofrecidos por la empresa.", "order" => 2, "dom_id" => "menu-items-services", "dom_label" => "Servicios", "dom_route" => "services.index"],
            ["id" => 52, "section_id" => 5, "slug" => "sc_items-subscriptions", "name" => "items-subscriptions", "description" => "Define planes de membresía, duración y precio de venta.", "order" => 3, "dom_id" => "menu-items-subscriptions", "dom_label" => "Membresías", "dom_route" => "subscriptions.index"],
            ["id" => 53, "section_id" => 5, "slug" => "sc_items-categories", "name" => "items-categories", "description" => "Organiza productos y servicios mediante categorías.", "order" => 4, "dom_id" => "menu-items-categories", "dom_label" => "Categorías", "dom_route" => "categories.index"],
            ["id" => 55, "section_id" => 5, "slug" => "sc_items-brands", "name" => "items-brands", "description" => "Administra las marcas utilizadas para identificar y agrupar productos.", "order" => 5, "dom_id" => "menu-items-brands", "dom_label" => "Marcas", "dom_route" => "brands.index"],
            ["id" => 59, "section_id" => 5, "slug" => "sc_items-recipes", "name" => "items-recipes", "description" => "Configura recetas, platillos, toppings, sabores e insumos para negocios de comida.", "order" => 6, "dom_id" => "menu-items-recipes", "dom_label" => "Recetas y platillos", "dom_route" => "recipes.index"],
            // Inventory
            ["id" => 54, "section_id" => 11, "slug" => "sc_inventory-stock", "name" => "inventory-stock", "description" => "Consulta existencias actuales, mínimos y alertas por almacén.", "order" => 1, "dom_id" => "menu-inventory-stock", "dom_label" => "Control de stock", "dom_route" => "stocks_management.stock.index"],
            ["id" => 56, "section_id" => 11, "slug" => "sc_inventory-kardex", "name" => "inventory-kardex", "description" => "Consulta la trazabilidad de entradas, salidas, correcciones y saldos resultantes.", "order" => 2, "dom_id" => "menu-inventory-kardex", "dom_label" => "Kardex", "dom_route" => "stocks_management.kardex.index"],
            ["id" => 57, "section_id" => 11, "slug" => "sc_inventory-transfers", "name" => "inventory-transfers", "description" => "Registra y consulta traslados de productos entre almacenes.", "order" => 3, "dom_id" => "menu-inventory-transfers", "dom_label" => "Traslados", "dom_route" => "stocks_management.transfers.index"],
            ["id" => 107, "section_id" => 11, "slug" => "sc_inventory-guides", "name" => "inventory-guides", "description" => "Consulta guías numeradas de entrada y salida con estado, almacén y detalle.", "order" => 4, "dom_id" => "menu-inventory-guides", "dom_label" => "Guías", "dom_route" => "stocks_management.guides.index"],
            ["id" => 58, "section_id" => 11, "slug" => "sc_inventory-valued", "name" => "inventory-valued", "description" => "Consulta el kardex valorizado con costo unitario, valor de movimiento y saldo valorizado.", "order" => 5, "dom_id" => "menu-inventory-valued", "dom_label" => "Kardex valorizado", "dom_route" => "stocks_management.valued.index"],

            // Cash registers
            ["id" => 100, "section_id" => 10, "slug" => "sc_cash-registers", "name" => "cash-registers", "description" => "Gestiona cajas configuradas por sucursal y su estado operativo.", "order" => 2, "dom_id" => "menu-cash-registers", "dom_label" => "Cajas", "dom_route" => "cash_registers.registers.index"],
            ["id" => 101, "section_id" => 10, "slug" => "sc_cash-sessions", "name" => "cash-sessions", "description" => "Consulta aperturas, cierres, arqueos y diferencias por caja.", "order" => 3, "dom_id" => "menu-cash-sessions", "dom_label" => "Aperturas y cierres", "dom_route" => "cash_registers.sessions.index"],
            ["id" => 102, "section_id" => 10, "slug" => "sc_cash-movements", "name" => "cash-movements", "description" => "Registra y consulta entradas, salidas y ajustes manuales de dinero.", "order" => 4, "dom_id" => "menu-cash-movements", "dom_label" => "Movimientos", "dom_route" => "cash_registers.movements.index"],
            ["id" => 103, "section_id" => 10, "slug" => "sc_cash-summary", "name" => "cash-summary", "description" => "Revisa resumen de caja por métodos de pago, esperado, contado y diferencia.", "order" => 5, "dom_id" => "menu-cash-summary", "dom_label" => "Resumen", "dom_route" => "cash_registers.summary.index"],
            ["id" => 104, "section_id" => 10, "slug" => "sc_user-attendances", "name" => "user-attendances", "description" => "Controla ingresos, salidas y horas trabajadas por colaborador.", "order" => 6, "dom_id" => "menu-user-attendances", "dom_label" => "Asistencia del personal", "dom_route" => "user_attendances.index"],
            ["id" => 105, "section_id" => 10, "slug" => "sc_restaurant-pos", "name" => "restaurant-pos", "description" => "Gestiona mesas, pedidos en curso y su posterior cobro en POS.", "order" => 7, "dom_id" => "menu-restaurant-pos", "dom_label" => "Restaurante POS", "dom_route" => "restaurant_pos.index"],
            ["id" => 106, "section_id" => 10, "slug" => "sc_service-sessions", "name" => "service-sessions", "description" => "Inicia, asigna y finaliza servicios midiendo su tiempo real.", "order" => 8, "dom_id" => "menu-service-sessions", "dom_label" => "Servicios en curso", "dom_route" => "service_sessions.index"],

            // Infrastructure
            ["id" => 60, "section_id" => 6, "slug" => "sc_infrastructure-branches", "name" => "infrastructure-branches", "description" => "Administra sedes, datos de contacto y capacidad.", "order" => 1, "dom_id" => "menu-infrastructure-branches", "dom_label" => "Sucursales", "dom_route" => "branches.index"],
            ["id" => 61, "section_id" => 6, "slug" => "sc_infrastructure-assets", "name" => "infrastructure-assets", "description" => "Mantén el catálogo de equipos y bienes de la empresa.", "order" => 2, "dom_id" => "menu-infrastructure-assets", "dom_label" => "Activos", "dom_route" => "assets.index"],
            ["id" => 62, "section_id" => 6, "slug" => "sc_infrastructure-assets_management", "name" => "infrastructure-assets_management", "description" => "Asigna, traslada y controla activos por sede o usuario.", "order" => 3, "dom_id" => "menu-infrastructure-assets_management", "dom_label" => "Gestión de activos", "dom_route" => "assets_management.index"],
            ["id" => 63, "section_id" => 6, "slug" => "sc_infrastructure-biometric_devices", "name" => "infrastructure-biometric_devices", "description" => "Configura los dispositivos usados para control biométrico.", "order" => 4, "dom_id" => "menu-infrastructure-biometric_devices", "dom_label" => "Dispositivos biométricos", "dom_route" => "biometric_devices.index"],

            // Configuration
            ["id" => 70, "section_id" => 7, "slug" => "sc_configuration-my_company", "name" => "configuration-my_company", "description" => "Actualiza la identidad y los datos generales de la empresa.", "order" => 1, "dom_id" => "menu-configuration-my_company", "dom_label" => "Mi empresa", "dom_route" => "companies.index"],
            ["id" => 71, "section_id" => 7, "slug" => "sc_configuration-users", "name" => "configuration-users", "description" => "Administra usuarios internos, roles y accesos.", "order" => 2, "dom_id" => "menu-configuration-users", "dom_label" => "Colaboradores", "dom_route" => "users.index"],
            ["id" => 72, "section_id" => 7, "slug" => "sc_configuration-roles", "name" => "configuration-roles", "description" => "Define perfiles de acceso y módulos disponibles para cada colaborador.", "order" => 3, "dom_id" => "menu-configuration-roles", "dom_label" => "Perfiles de acceso", "dom_route" => "roles.index"],

            // Reports
            ["id" => 80, "section_id" => 8, "slug" => "sc_reports", "name" => "reports", "description" => "Genera consultas y reportes para análisis operativo.", "order" => 1, "dom_id" => "menu-reports", "dom_label" => "Reportes", "dom_route" => "reports.index"],
        ]);

        DB::table("companies_sub_sections")->insert([
            // ["company_id" => 1, "sub_section_id" => 10],
            ["company_id" => 1, "sub_section_id" => 20],
            ["company_id" => 1, "sub_section_id" => 30],
            ["company_id" => 1, "sub_section_id" => 31],
            ["company_id" => 1, "sub_section_id" => 32],
            ["company_id" => 1, "sub_section_id" => 90],
            ["company_id" => 1, "sub_section_id" => 92],
            ["company_id" => 1, "sub_section_id" => 91],
            ["company_id" => 1, "sub_section_id" => 40],
            ["company_id" => 1, "sub_section_id" => 41],
            ["company_id" => 1, "sub_section_id" => 42],
            ["company_id" => 1, "sub_section_id" => 43],
            ["company_id" => 1, "sub_section_id" => 44],
            ["company_id" => 1, "sub_section_id" => 45],
            ["company_id" => 1, "sub_section_id" => 50],
            ["company_id" => 1, "sub_section_id" => 51],
            ["company_id" => 1, "sub_section_id" => 52],
            ["company_id" => 1, "sub_section_id" => 53],
            ["company_id" => 1, "sub_section_id" => 55],
            ["company_id" => 1, "sub_section_id" => 59],
            ["company_id" => 1, "sub_section_id" => 54],
            ["company_id" => 1, "sub_section_id" => 56],
            ["company_id" => 1, "sub_section_id" => 57],
            ["company_id" => 1, "sub_section_id" => 107],
            ["company_id" => 1, "sub_section_id" => 58],
            ["company_id" => 1, "sub_section_id" => 100],
            ["company_id" => 1, "sub_section_id" => 101],
            ["company_id" => 1, "sub_section_id" => 102],
            ["company_id" => 1, "sub_section_id" => 103],
            ["company_id" => 1, "sub_section_id" => 104],
            ["company_id" => 1, "sub_section_id" => 105],
            ["company_id" => 1, "sub_section_id" => 106],
            ["company_id" => 1, "sub_section_id" => 60],
            ["company_id" => 1, "sub_section_id" => 61],
            ["company_id" => 1, "sub_section_id" => 62],
            ["company_id" => 1, "sub_section_id" => 63],
            ["company_id" => 1, "sub_section_id" => 70],
            ["company_id" => 1, "sub_section_id" => 71],
            ["company_id" => 1, "sub_section_id" => 72],
            // ["company_id" => 1, "sub_section_id" => 80]
        ]);


        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [10])->update(["section_order" => 1]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [20])->update(["section_order" => 2]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [32, 100, 101, 102, 103, 104, 105, 106])->update(["section_order" => 3]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [30, 31])->update(["section_order" => 4]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [90, 92, 91])->update(["section_order" => 5]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [40, 41, 42, 43, 44, 45])->update(["section_order" => 6]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [50, 51, 52, 53, 55, 59])->update(["section_order" => 7]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [54, 56, 57, 107, 58])->update(["section_order" => 8]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [60, 61, 62, 63])->update(["section_order" => 9]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [70, 71, 72])->update(["section_order" => 10]);
        DB::table("companies_sub_sections")->where("company_id", 1)->whereIn("sub_section_id", [80])->update(["section_order" => 11]);

        $companySubSectionOrders = [
            10 => 1,
            20 => 1,
            32 => 1,
            100 => 2,
            101 => 3,
            102 => 4,
            103 => 5,
            104 => 6,
            105 => 7,
            106 => 8,
            30 => 1,
            31 => 2,
            90 => 1,
            92 => 2,
            91 => 3,
            40 => 1,
            41 => 2,
            42 => 3,
            43 => 4,
            44 => 5,
            45 => 6,
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
            60 => 1,
            61 => 2,
            62 => 3,
            63 => 4,
            70 => 1,
            71 => 2,
            72 => 3,
            80 => 1
        ];

        foreach($companySubSectionOrders as $subSectionId => $subSectionOrder) {

            DB::table("companies_sub_sections")
                ->where("company_id", 1)
                ->where("sub_section_id", $subSectionId)
                ->update(["sub_section_order" => $subSectionOrder]);

        }

        DB::table("roles")->insert([
            ["id" => 1, "company_id" => 1, "slug" => Utilities::generateCode(), "name" => "Administrador", "is_full_access" => true],
            ["id" => 2, "company_id" => 1, "slug" => Utilities::generateCode(), "name" => "Vendedor", "is_full_access" => false]
        ]);

        DB::table("role_sub_sections")->insert([
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 20],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 30],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 31],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 32],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 90],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 92],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 40],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 50],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 51],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 52],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 53],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 55],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 59],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 54],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 56],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 57],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 107],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 58],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 100],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 101],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 102],
            ["company_id" => 1, "role_id" => 2, "sub_section_id" => 103]
        ]);

        DB::table("users")->insert([
            ["company_id" => 1, "role_id" => 1, "identity_document_type_id" => 1, "document_number" => "999999999", "name" => "Usuario demo", "email" => "demo@pagape.com", "password" => Hash::make("1")],
            ["company_id" => 1, "role_id" => 2, "identity_document_type_id" => 2, "document_number" => "71883137", "name" => "Gianfranco", "email" => "gmc@pagape.com", "password" => Hash::make("1")]
        ]);
        DB::table("company_settings")->insert([
            [
                "company_id" => 1,
                "group" => "localization",
                "key" => "timezone",
                "value" => "America/Lima",
                "description" => "Zona horaria IANA usada para convertir los límites diarios del Dashboard y otros procesos operativos. Las fechas se almacenan en la base de datos y se interpretan con esta zona antes de calcular indicadores por día.",
                "value_type" => "string"
            ],
            [
                "company_id" => 1,
                "group" => "dashboard",
                "key" => "membership_expiration_window_days",
                "value" => "7",
                "description" => "Cantidad de días calendario, incluyendo la fecha consultada, usada para identificar membresías activas próximas a vencer en el Dashboard.",
                "value_type" => "integer"
            ],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "product", "value" => "PRO", "description" => "Prefijo usado para generar códigos internos de productos. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "service", "value" => "SER", "description" => "Prefijo usado para generar códigos internos de servicios. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "subscription", "value" => "MEM", "description" => "Prefijo usado para generar códigos internos de membresías. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "brand", "value" => "MAR", "description" => "Prefijo usado para generar códigos internos de marcas. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "category", "value" => "CAT", "description" => "Prefijo usado para generar códigos internos de categorías. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "branch", "value" => "SUC", "description" => "Prefijo usado para generar códigos internos de sucursales. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "asset", "value" => "ACT", "description" => "Prefijo usado para generar códigos internos de activos. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            ["company_id" => 1, "group" => "internal_code_prefixes", "key" => "recipe", "value" => "REC", "description" => "Prefijo sugerido para identificar recetas, platillos y configuraciones operativas de cocina. Si el valor queda vacío, el código se guarda sin prefijo.", "value_type" => "string"],
            [
                "company_id" => 1,
                "group" => "inventory",
                "key" => "allow_negative_stock_on_sale",
                "value" => "false",
                "description" => "Define si una venta normal o una venta POS/caja puede dejar productos con stock negativo. Por defecto es false: si la salida supera el stock disponible, la venta se bloquea antes de confirmar.",
                "value_type" => "boolean"
            ],
            [
                "company_id" => 1,
                "group" => "inventory",
                "key" => "restore_stock_on_sale_cancellation",
                "value" => "false",
                "description" => "Define si al anular una venta se devuelven automáticamente los productos al almacén original. Por defecto es false: la anulación no repone stock y la devolución física debe registrarse desde Inventario si corresponde.",
                "value_type" => "boolean"
            ],
            [
                "company_id" => 1,
                "group" => "inventory",
                "key" => "valuation_method",
                "value" => "weighted_average",
                "description" => "Método usado para valorizar inventario y kardex. El valor inicial weighted_average calcula costo promedio ponderado sobre entradas y saldos.",
                "value_type" => "string"
            ],
            [
                "company_id" => 1,
                "group" => "customer_attendance",
                "key" => "daily_limit_scope",
                "value" => "branch",
                "description" => "Define si el límite diario de asistencia de clientes se cuenta por sucursal (branch) o sumando todas las sucursales de la empresa (company).",
                "value_type" => "string"
            ],
            [
                "company_id" => 1,
                "group" => "customer_attendance",
                "key" => "biometric_duplicate_tolerance_seconds",
                "value" => "10",
                "description" => "Ventana mínima entre lecturas biométricas equivalentes del mismo cliente y dispositivo. Evita duplicados provocados por reintentos del lector.",
                "value_type" => "integer"
            ],
            [
                "company_id" => 1,
                "group" => "customer_attendance",
                "key" => "allow_automatic_checkout",
                "value" => "false",
                "description" => "Permite que una lectura QR o biométrica finalice automáticamente una asistencia activa de cliente. El valor inicial false evita salidas involuntarias.",
                "value_type" => "boolean"
            ],
            [
                "company_id" => 1,
                "group" => "subscriptions",
                "key" => "overlap_policy",
                "value" => "block",
                "description" => "Política para membresías vigentes superpuestas del mismo cliente: block rechaza el solapamiento y allow lo permite explícitamente.",
                "value_type" => "string"
            ],
            [
                "company_id" => 1,
                "group" => "reports",
                "key" => "export_max_rows",
                "value" => "25000",
                "description" => "Máximo de filas permitido por archivo exportado. Obliga a reducir el rango antes de que una consulta excesiva agote memoria o tiempo de ejecución.",
                "value_type" => "integer"
            ],
            [
                "company_id" => 1,
                "group" => "cash",
                "key" => "require_open_session_on_sale",
                "value" => "false",
                "description" => "Cuando está activo, toda venta debe vincularse a una sesión de caja abierta de la misma sucursal. Cuando está inactivo, las ventas administrativas pueden registrarse sin caja.",
                "value_type" => "boolean"
            ]
        ]);

        DB::table("taxes")->insert([
            [
                "company_id" => 1,
                "code" => "SALE-IGV",
                "name" => "IGV",
                "description" => "Impuesto General a las Ventas del Perú aplicado a ventas. Si el item incluye IGV, se calcula como tributo contenido; si no lo incluye, se suma al total.",
                "rate" => 18,
                "calculation_type" => "percentage",
                "operation_type" => "addition",
                "min_apply_quantity" => null,
                "max_apply_quantity" => null,
                "scope" => "sale",
                "is_required" => true,
                "is_default" => true
            ],
            [
                "company_id" => 1,
                "code" => "SALE-ICBP",
                "name" => "ICBP",
                "description" => "Impuesto al Consumo de Bolsas Plásticas aplicado a ventas cuando corresponde. Es opcional porque no todas las ventas incluyen bolsa gravada.",
                "rate" => 0.5,
                "calculation_type" => "fixed",
                "operation_type" => "addition",
                "min_apply_quantity" => 0,
                "max_apply_quantity" => null,
                "scope" => "sale",
                "is_required" => false,
                "is_default" => false
            ],
            [
                "company_id" => 1,
                "code" => "PURCHASE-IGV",
                "name" => "IGV",
                "description" => "Impuesto General a las Ventas del Perú aplicado a compras. Se calcula sobre la base de compra registrada.",
                "rate" => 18,
                "calculation_type" => "percentage",
                "operation_type" => "addition",
                "min_apply_quantity" => null,
                "max_apply_quantity" => null,
                "scope" => "purchase",
                "is_required" => true,
                "is_default" => true
            ],
            [
                "company_id" => 1,
                "code" => "PURCHASE-ICBP",
                "name" => "ICBP",
                "description" => "Impuesto al Consumo de Bolsas Plásticas aplicado a compras cuando corresponde. Es opcional porque no todas las compras incluyen bolsa gravada.",
                "rate" => 0.5,
                "calculation_type" => "fixed",
                "operation_type" => "addition",
                "min_apply_quantity" => 0,
                "max_apply_quantity" => null,
                "scope" => "purchase",
                "is_required" => false,
                "is_default" => false
            ]
        ]);

        DB::table("payment_methods")->insert([
            ["company_id" => 1, "code" => "CASH", "sunat_code" => "008", "name" => "Efectivo", "image_path" => "System/assets/img/payment-methods/cash.svg", "scope" => "both", "requires_reference" => false, "is_default" => true],
            ["company_id" => 1, "code" => "BANK_DEPOSIT", "sunat_code" => "001", "name" => "Depósito en cuenta", "image_path" => "System/assets/img/payment-methods/bank-deposit.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "BANK_TRANSFER", "sunat_code" => "003", "name" => "Transferencia de fondos", "image_path" => "System/assets/img/payment-methods/bank-transfer.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "DEBIT_CARD", "sunat_code" => "005", "name" => "Tarjeta de débito", "image_path" => "System/assets/img/payment-methods/debit-card.svg", "scope" => "sale", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "CREDIT_CARD", "sunat_code" => "006", "name" => "Tarjeta de crédito", "image_path" => "System/assets/img/payment-methods/credit-card.svg", "scope" => "sale", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "CHECK", "sunat_code" => "007", "name" => "Cheque no negociable", "image_path" => "System/assets/img/payment-methods/check.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "DIGITAL_WALLET", "sunat_code" => null, "name" => "Billetera digital", "image_path" => "System/assets/img/payment-methods/digital-wallet.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "YAPE", "sunat_code" => null, "name" => "Yape", "image_path" => "System/assets/img/payment-methods/yape.svg", "scope" => "both", "requires_reference" => true, "is_default" => false],
            ["company_id" => 1, "code" => "PLIN", "sunat_code" => null, "name" => "Plin", "image_path" => "System/assets/img/payment-methods/plin.svg", "scope" => "both", "requires_reference" => true, "is_default" => false]
        ]);

        DB::table("company_socials_media")->insert([
            ["company_id" => 1, "type" => "facebook", "link" => "https://www.facebook.com/GianfrancoMC"],
            ["company_id" => 1, "type" => "instagram", "link" => "https://www.instagram.com/gianfrancomc"],
            ["company_id" => 1, "type" => "whatsapp", "link" => "https://wa.me/987057624"]
        ]);

        DB::table("branches")->insert([
            ["id" => 1, "internal_code" => "SUC-" . Utilities::generateCode(5), "company_id" => 1, "name" => "Sede Principal"]
        ]);

        DB::table("series")->insert([
            ["company_id" => 1, "branch_id" => 1, "document_type_id" => 1, "code" => "BV", "number" => 1, "init" => 1],
            ["company_id" => 1, "branch_id" => 1, "document_type_id" => 2, "code" => "FA", "number" => 1, "init" => 1]
        ]);

        DB::table("customers")->insert([
            ["company_id" => 1, "identity_document_type_id" => 1, "document_number" => "999999999", "name" => "Cliente varios", "phone_number" => ""],
            ["company_id" => 1, "identity_document_type_id" => 2, "document_number" => "71883137", "name" => "Gianfranco Mejia Carhuajulca", "phone_number" => "51987057624"],
            ["company_id" => 1, "identity_document_type_id" => 2, "document_number" => "71883136", "name" => "Andy Paolo Mejia Carhuajulca", "phone_number" => "51987634253"]
        ]);

        DB::table("warehouses")->insert([
            ["company_id" => 1, "branch_id" => 1, "name" => "Almacén - Sede principal"]
        ]);

        DB::table("biometric_device_brands")->insert([
            ["id" => 1, "company_id" => 1, "slug" => "zkteco", "name" => "ZKTeco", "status" => "active"]
        ]);

        DB::table("biometric_device_models")->insert([
            ["id" => 1, "company_id" => 1, "biometric_device_brand_id" => 1, "slug" => "k20-pro", "name" => "K20 Pro", "status" => "active"]
        ]);

        DB::table("cash_registers")->insert([
            ["company_id" => 1, "branch_id" => 1, "code" => "CAJ-" . Utilities::generateCode(5), "name" => "Caja principal", "is_main" => true]
        ]);
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void {
        DB::table("cash_registers")->where("company_id", 1)->delete();
        DB::table("biometric_device_models")->where("company_id", 1)->delete();
        DB::table("biometric_device_brands")->where("company_id", 1)->delete();
        DB::table("warehouses")->where("company_id", 1)->delete();
        DB::table("customers")->where("company_id", 1)->delete();
        DB::table("series")->where("company_id", 1)->delete();
        DB::table("branches")->where("company_id", 1)->delete();
        DB::table("company_socials_media")->where("company_id", 1)->delete();
        DB::table("payment_methods")->where("company_id", 1)->delete();
        DB::table("taxes")->where("company_id", 1)->delete();
        DB::table("company_settings")->where("company_id", 1)->delete();
        DB::table("users")->where("company_id", 1)->delete();
        DB::table("role_sub_sections")->where("company_id", 1)->delete();
        DB::table("roles")->where("company_id", 1)->delete();
        DB::table("companies_sub_sections")->where("company_id", 1)->delete();
        DB::table("sub_sections")->whereBetween("id", [10, 103])->delete();
        DB::table("sections")->whereBetween("id", [1, 9])->delete();
        DB::table("companies")->where("id", 1)->delete();
        DB::table("currencies")->where("company_id", 1)->delete();
        DB::table("document_types")->where("company_id", 1)->delete();
        DB::table("identity_document_types")->where("company_id", 1)->delete();
    }
};
