<?php

use App\Helpers\System\Utilities;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Hash, Schema};

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {

        // ✅
        Schema::create("identity_document_types", function(Blueprint $table) {
            $table->id();
            $table->string("code");
            $table->string("name");
            $table->boolean("is_searchable")->default(true);
            $table->integer("min_length")->default(1);
            $table->integer("max_length")->default(50);

            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
        });

        // ✅
        Schema::create("document_types", function(Blueprint $table) {
            $table->id();
            $table->string("code");
            $table->string("name");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
        });

        // ✅
        Schema::create("currencies", function(Blueprint $table) {
            $table->id();
            $table->string("code");
            $table->string("sign");
            $table->string("singular_name");
            $table->string("plural_name");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
        });

        // ✅
        Schema::create("companies", function(Blueprint $table) {
            $table->id();
            $table->string("slug")->unique();
            $table->string("internal_code");
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number");
            $table->string("legal_name");
            $table->string("commercial_name");
            $table->unsignedBigInteger("currency_id");
            $table->string("tagline")->nullable();
            $table->string("description", 500)->nullable();
            $table->string("address")->nullable();
            $table->string("telephone")->nullable();
            $table->string("email")->nullable();
            $table->string("token_api_misc")->nullable();
            $table->string("logotype")->nullable();
            $table->string("combinationmark")->nullable();
            $table->string("logomark")->nullable();
            $table->string("login_image")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->onDelete("cascade");
            $table->foreign("currency_id")->references("id")->on("currencies")->onDelete("cascade");
        });

        // ✅
        Schema::create("sections", function(Blueprint $table) {
            $table->id();
            $table->string("slug");
            $table->string("name");
            $table->integer("order")->nullable();
            $table->string("dom_id")->default("");
            $table->string("dom_label")->default("");
            $table->string("dom_icon")->default("");
            $table->boolean("has_sub_menu")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();
        });

        // ✅
        Schema::create("sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("section_id");
            $table->string("slug");
            $table->string("name");
            $table->string("description", 255)->nullable();
            $table->integer("order")->nullable();
            $table->string("dom_id")->default("");
            $table->string("dom_label")->default("");
            $table->string("dom_icon")->default("");
            $table->string("dom_route")->default("");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("section_id")->references("id")->on("sections")->onDelete("cascade");
        });

        // ✅
        Schema::create("companies_sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("sub_section_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("sub_section_id")->references("id")->on("sub_sections")->onDelete("cascade");
        });

        // ✅
        Schema::create("roles", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("slug");
            $table->string("name");
            $table->boolean("is_full_access")->default(false);
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
        });

        // ✅
        Schema::create("role_sub_sections", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("role_id");
            $table->unsignedBigInteger("sub_section_id");
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("sub_section_id")->references("id")->on("sub_sections")->onDelete("cascade");
            $table->unique(["role_id", "sub_section_id"]);
        });

        // ✅
        Schema::create("users", function(Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("role_id")->nullable();
            $table->unsignedBigInteger("identity_document_type_id");
            $table->string("document_number");
            $table->string("name");
            $table->string("email");
            $table->timestamp("email_verified_at")->nullable();
            $table->string("password");
            $table->rememberToken();
            $table->string("phone_number")->nullable();
            $table->enum("gender", ["male", "female", "other"])->nullable();
            $table->date("birthdate")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("company_id")->references("id")->on("companies")->onDelete("cascade");
            $table->foreign("role_id")->references("id")->on("roles")->onDelete("cascade");
            $table->foreign("identity_document_type_id")->references("id")->on("identity_document_types")->onDelete("cascade");
            $table->unique(["email", "company_id"]);
        });

        // ✅
        Schema::create("user_preferences", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string("slug");
            $table->text("value")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");

            $table->timestamp("created_at")->useCurrent()->nullable();
            $table->integer("created_by")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->integer("updated_by")->nullable();

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
        });

        // Inserts
        DB::table("identity_document_types")->insert([
            ["id" => 1, "code" => "doc.trib.no.dom.sin.ruc", "name" => "Doc.trib.no.dom.sin.ruc", "is_searchable" => false, "min_length" => 15, "max_length" => 15],
            ["id" => 2, "code" => "dni", "name" => "DNI", "is_searchable" => true, "min_length" => 8, "max_length" => 8],
            ["id" => 3, "code" => "ce", "name" => "CE", "is_searchable" => false, "min_length" => 12, "max_length" => 12],
            ["id" => 4, "code" => "ruc", "name" => "RUC", "is_searchable" => true, "min_length" => 11, "max_length" => 11],
            ["id" => 5, "code" => "pasaporte", "name" => "Pasaporte", "is_searchable" => false, "min_length" => 8, "max_length" => 8]
        ]);

        DB::table("document_types")->insert([
            ["id" => 1, "code" => "BV", "name" => "BOLETA DE VENTA"],
            ["id" => 2, "code" => "FA", "name" => "FACTURA"]
        ]);

        DB::table("currencies")->insert([
            ["id" => 1, "code" => "PEN", "sign" => "S/", "singular_name" => "SOL", "plural_name" => "SOLES"]
        ]);

        DB::table("companies")->insert([
            [
                "id" => 1,
                "slug" => "pagape",
                "internal_code" => Utilities::generateCode(7),
                "identity_document_type_id" => 1,
                "document_number" => "999999999999999",
                "legal_name" => "BLAPOS S.A.",
                "commercial_name" => "BLAPOS",
                "currency_id" => 1,
                "address" => "",
                "telephone" => "",
                "email" => ""
            ]
        ]);

        DB::table("sections")->insert([
            ["id" => 1, "slug" => "sc_home", "name" => "home", "order" => 1, "dom_id" => "menu-parent-home", "dom_label" => "Inicio", "dom_icon" => "fa fa-home", "has_sub_menu" => false],
            ["id" => 2, "slug" => "sc_dashboard", "name" => "dashboard", "order" => 2, "dom_id" => "menu-parent-dashboard", "dom_label" => "Dashboard", "dom_icon" => "fa-solid fa-gauge", "has_sub_menu" => false],
            ["id" => 3, "slug" => "sc_sales", "name" => "sales", "order" => 3, "dom_id" => "menu-parent-sales", "dom_label" => "Ventas", "dom_icon" => "fa-solid fa-cash-register", "has_sub_menu" => true],
            ["id" => 9, "slug" => "sc_purchases", "name" => "purchases", "order" => 4, "dom_id" => "menu-parent-purchases", "dom_label" => "Compras", "dom_icon" => "fa-solid fa-cart-flatbed", "has_sub_menu" => true],
            ["id" => 4, "slug" => "sc_customers", "name" => "customers", "order" => 5, "dom_id" => "menu-parent-customers", "dom_label" => "Gestión de clientes", "dom_icon" => "fa fa-user", "has_sub_menu" => true],
            ["id" => 5, "slug" => "sc_items", "name" => "items", "order" => 6, "dom_id" => "menu-parent-items", "dom_label" => "Catálogo comercial", "dom_icon" => "fa fa-book", "has_sub_menu" => true],
            ["id" => 10, "slug" => "sc_cash_registers", "name" => "cash_registers", "order" => 7, "dom_id" => "menu-parent-cash-registers", "dom_label" => "Caja", "dom_icon" => "fa-solid fa-vault", "has_sub_menu" => true],
            ["id" => 6, "slug" => "sc_infrastructure", "name" => "infrastructure", "order" => 8, "dom_id" => "menu-parent-infrastructure", "dom_label" => "Infraestructura", "dom_icon" => "fa-solid fa-industry", "has_sub_menu" => true],
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

            ["id" => 32, "section_id" => 3, "slug" => "sc_sales-pos", "name" => "sales-pos", "description" => "Venta rÃ¡pida para mostrador, vinculada a almacÃ©n y caja activa.", "order" => 3, "dom_id" => "menu-sales-pos", "dom_label" => "Venta POS", "dom_route" => "sales.pos"],

            // Purchases
            ["id" => 90, "section_id" => 9, "slug" => "sc_purchases-list", "name" => "purchases-list", "description" => "Registra órdenes y facturas, controla recepciones y costos de inventario.", "order" => 1, "dom_id" => "menu-purchases-list", "dom_label" => "Compras", "dom_route" => "purchases.index"],
            ["id" => 91, "section_id" => 9, "slug" => "sc_purchases-suppliers", "name" => "purchases-suppliers", "description" => "Administra proveedores y sus datos comerciales.", "order" => 2, "dom_id" => "menu-purchases-suppliers", "dom_label" => "Proveedores", "dom_route" => "suppliers.index"],

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
            ["id" => 54, "section_id" => 5, "slug" => "sc_items-stocks_management", "name" => "items-stocks_management", "description" => "Consulta existencias, registra ajustes y revisa el kardex por almacén.", "order" => 6, "dom_id" => "menu-items-stocks_management", "dom_label" => "Inventario", "dom_route" => "stocks_management.index"],

            // Cash registers
            ["id" => 100, "section_id" => 10, "slug" => "sc_cash-registers", "name" => "cash-registers", "description" => "Gestiona aperturas, cierres, arqueos, resumen por mÃ©todo de pago y movimientos de caja.", "order" => 1, "dom_id" => "menu-cash-registers", "dom_label" => "Caja", "dom_route" => "cash_registers.index"],

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
            ["company_id" => 1, "sub_section_id" => 54],
            ["company_id" => 1, "sub_section_id" => 100],
            ["company_id" => 1, "sub_section_id" => 60],
            ["company_id" => 1, "sub_section_id" => 61],
            ["company_id" => 1, "sub_section_id" => 62],
            ["company_id" => 1, "sub_section_id" => 63],
            ["company_id" => 1, "sub_section_id" => 70],
            ["company_id" => 1, "sub_section_id" => 71],
            ["company_id" => 1, "sub_section_id" => 72],
            // ["company_id" => 1, "sub_section_id" => 80]
        ]);

        DB::table("roles")->insert([
            ["id" => 1, "company_id" => 1, "slug" => Utilities::generateCode(), "name" => "Administrador", "is_full_access" => true],
            ["id" => 2, "company_id" => 1, "slug" => Utilities::generateCode(), "name" => "Vendedor", "is_full_access" => false]
        ]);

        DB::table("role_sub_sections")->insert([
            ["role_id" => 2, "sub_section_id" => 20],
            ["role_id" => 2, "sub_section_id" => 30],
            ["role_id" => 2, "sub_section_id" => 31],
            ["role_id" => 2, "sub_section_id" => 32],
            ["role_id" => 2, "sub_section_id" => 40],
            ["role_id" => 2, "sub_section_id" => 50],
            ["role_id" => 2, "sub_section_id" => 51],
            ["role_id" => 2, "sub_section_id" => 52],
            ["role_id" => 2, "sub_section_id" => 53],
            ["role_id" => 2, "sub_section_id" => 55],
            ["role_id" => 2, "sub_section_id" => 100]
        ]);

        DB::table("users")->insert([
            ["company_id" => 1, "role_id" => 1, "identity_document_type_id" => 1, "document_number" => "999999999", "name" => "Usuario demo", "email" => "demo@pagape.com", "password" => Hash::make("1")],
            ["company_id" => 1, "role_id" => 2, "identity_document_type_id" => 2, "document_number" => "71883137", "name" => "Gianfranco", "email" => "gmc@pagape.com", "password" => Hash::make("1")]
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {

        Schema::dropIfExists("user_preferences");
        Schema::dropIfExists("users");
        Schema::dropIfExists("role_sub_sections");
        Schema::dropIfExists("roles");
        Schema::dropIfExists("companies_sub_sections");
        Schema::dropIfExists("sub_sections");
        Schema::dropIfExists("sections");
        Schema::dropIfExists("companies");
        Schema::dropIfExists("currencies");
        Schema::dropIfExists("document_types");
        Schema::dropIfExists("identity_document_types");

    }

};
