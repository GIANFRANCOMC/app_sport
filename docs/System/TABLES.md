# System - Tablas y relaciones

Este archivo describe las tablas creadas por migraciones y usadas por System. Algunas tablas tambien son leidas por Guest, pero la administracion principal pertenece a System.


## Landlord multi-tenant

Estas tablas viven en la conexión `landlord`, no en cada BD tenant. Resuelven exclusivamente subdominios registrados hacia una base de datos aislada.

### platform_users

Administradores exclusivos de `app.<TENANCY_BASE_DOMAIN>`. Campos: `name`, `email`, `password`, `status`, `session_version`, `last_login_at` y `last_login_ip`.

No se relaciona con `users` de los tenants. `session_version` permite revocar todas las sesiones al rotar credenciales mediante `platform:admin`.

### tenant_databases

Registro central de tenants. Campos: `slug`, `company_id`, `database_name`, `status` y `last_resolved_at`.

`company_id` es el ID raíz esperado dentro de la BD tenant. No declara FK porque `companies` pertenece a la base tenant, no a landlord.

No almacena credenciales, host ni puerto. La conexión usa exclusivamente configuración segura del servidor. `database_name` debe cumplir el prefijo y formato definidos en `config/tenancy.php`.

### tenant_domains

Subdominios asociados a cada tenant. Campos: `tenant_database_id`, `domain`, `type`, `is_primary` y `status`. `type` se conserva como `subdomain`; dominios personalizados no son resueltos por esta aplicación.

Relaciones: pertenece a `tenant_databases`. `domain` debe ser único para evitar que dos clientes resuelvan al mismo host.
### tenant_audit_logs

Bitácora operativa de landlord. Campos: `tenant_database_id`, `company_id`, `action`, `result`, `host`, `ip_address`, `actor`, `context` y `occurred_at`.

Registra aprovisionamiento, verificación de salud, suspensión, reactivación, limpieza de caché, ejecución tenant del scheduler y rechazos contra hosts desconocidos. `context` contiene únicamente metadatos operativos; nunca credenciales ni identificadores de sesión en texto plano. La relación con `tenant_databases` es nullable para poder auditar hosts que todavía no corresponden a un tenant conocido.

### tenant_announcements

Avisos administrados desde landlord y visibles dentro de un tenant. Campos: `tenant_database_id`, `title`, `message`, `severity`, `starts_at`, `ends_at`, `dismissible`, `status`, `created_by` y `updated_by`.

Los avisos se filtran por tenant, vigencia y estado. El contenido se escapa al renderizarse y nunca habilita HTML arbitrario.

## Maestros generales

### identity_document_types

Tipos de documento de identidad configurables por empresa. Campos principales: `company_id`, `code`, `name`, `is_searchable`, `min_length`, `max_length` y `status`.

Relaciones: pertenece a `companies` mediante `company_id` y es usado por `companies`, `users`, `customers` y `book_complaints`. Se filtra siempre por empresa para que cada compañía pueda manejar longitudes, códigos y disponibilidad propia.
### document_types

Tipos de documentos comerciales o comprobantes configurables por empresa. Campos: `company_id`, `code`, `name` y `status`.

Relaciones: pertenece a `companies` mediante `company_id` y es usado por `series`. Al crear series de una sucursal sólo se toman comprobantes activos de la misma empresa.
### currencies

Monedas configurables por empresa. Campos: `company_id`, `code`, `sign`, `singular_name`, `plural_name` y `status`.

Relaciones: pertenece a `companies` mediante `company_id`; es usado por empresas, items, ventas, compras, activos y asignaciones. Las validaciones de catálogo, compras y ventas verifican que la moneda pertenezca a la empresa actual.
## Empresa, menu y usuarios

### companies

Empresa o tenant funcional. Campos: `slug`, `internal_code`, documento, razón social, nombre comercial, moneda, tagline, descripción, dirección, teléfono, email, token externo, imágenes y `status`.

`identity_document_type_id` y `currency_id` son nullable en la estructura inicial para permitir crear la empresa antes de sus maestros propios y luego actualizar las referencias. Esto evita ciclos frágiles entre `companies`, `identity_document_types` y `currencies`, manteniendo FK reales por `company_id` en todos los maestros configurables.
### `company_settings`

Configuración extensible por empresa. Cada registro usa `company_id`, `group`, `key`, `value`, `description`, `value_type` y `status`. `description` explica el efecto operativo de la clave a sus consumidores administrativos. `value` puede ser nulo y `value_type` permite interpretarlo como `string`, `boolean`, `integer`, `decimal` o `json`. El mantenimiento backend se expone como `company-settings` en `/master-data/{resource}`.

El grupo `internal_code_prefixes` contiene las claves `product`, `service`, `subscription`, `brand`, `category`, `branch`, `asset` y `recipe`. Sus valores iniciales son `PRO`, `SER`, `MEM`, `MAR`, `CAT`, `SUC`, `ACT` y `REC`. Un valor nulo o vacío desactiva el prefijo.

El grupo `numeric_validation` contiene `decimal_precision`, `default_min_value`, `default_max_value` y `max_file_size_kb`. Estas claves definen, por empresa, la cantidad de decimales aceptados, los límites operativos por defecto y el tamaño máximo de archivos en formularios. Los `FormRequest` deben leerlas mediante `CompanyFormRequest`; el frontend las recibe desde `config.generalConfig.forms.inputs` para mantener la misma experiencia visual.

El grupo `inventory` contiene `allow_negative_stock_on_sale`, booleano con valor predeterminado `false`. Cuando está desactivado, crear una venta normal o POS/caja se bloquea si la salida supera el stock disponible del almacén seleccionado. Cuando está activo, la venta puede dejar saldo negativo.

El grupo `inventory` también contiene `restore_stock_on_sale_cancellation`, booleano con valor predeterminado `false`. Cuando está desactivado, anular una venta no modifica existencias; una devolución física se registra posteriormente desde Inventario. Cuando está activo, la anulación repone automáticamente los productos en el almacén asociado a la venta.

El grupo `inventory` contiene `stock_alert_email_enabled` y `stock_alert_email_to`. La primera clave activa el correo de alerta cuando se abre una nueva alerta de stock bajo o en el mínimo; la segunda define el destinatario. Si el destinatario queda vacío, se usa el correo de la empresa cuando exista. Las actualizaciones de una alerta ya abierta no reenvían correo.

El grupo `external_api` contiene `document_lookup_monthly_warning_threshold`, entero usado para advertir cuando la empresa supera una cantidad mensual de consultas externas de DNI/RUC.

El grupo `customer_attendance` contiene reglas operativas para asistencias de clientes: `max_active_hours` limita asistencias abiertas demasiado antiguas, `auto_close_stale_enabled` habilita el cierre técnico, `auto_close_after_time` define desde qué hora puede ejecutarse el cierre de pendientes del día anterior, `auto_close_end_time` define la hora técnica de salida usada al cerrar automáticamente y `retention_months` define la retención mínima de historial. La retención nunca debe configurarse por debajo de cuatro meses.

El grupo `subscriptions` contiene `send_welcome_email_on_sale`, booleano que permite registrar un correo de agradecimiento cuando una venta genera una membresía real.

El grupo `loyalty` contiene `enabled` y `reverse_points_on_sale_cancellation`. `enabled` activa el sistema de puntos por empresa; `reverse_points_on_sale_cancellation` descuenta puntos previamente otorgados cuando una venta se anula.

El grupo `localization` contiene `timezone`, con valor inicial `America/Lima`. Debe almacenar una zona horaria IANA y se usa para construir límites diarios coherentes en Dashboard y procesos operativos.

El grupo `dashboard` contiene `membership_expiration_window_days`, entero con valor inicial `7`. Define cuántos días calendario, incluyendo la fecha consultada, abarca el KPI de membresías próximas a vencer.

El grupo `reports` contiene `sale_share_ttl_minutes`, entero con valor inicial `4320`. Define la vigencia de enlaces firmados para compartir, imprimir o enviar comprobantes de venta fuera de la sesión autenticada. Estos enlaces no exponen ids simples sin firma y Laravel rechaza cualquier alteración o vencimiento antes de renderizar el PDF.

La combinación `company_id + group + key` es única: una empresa no puede tener dos valores activos o históricos ambiguos para la misma configuración.

Relaciones: cada configuración pertenece a `companies` mediante `company_id`.

### external_api_request_logs

Bitácora mensual de consultas a proveedores externos. Campos: `company_id`, `user_id`, `service`, `action`, `document_type`, `document_number`, `result`, `ip_address` y `requested_at`.

Uso: permite medir cuántas solicitudes de DNI/RUC lleva una empresa por mes, mostrar advertencias cuando supera el umbral configurado y auditar bloqueos o fallos del proveedor. No almacena tokens ni secretos.

Relaciones: pertenece a `companies` y opcionalmente a `users`.

### company_socials_media

Redes sociales y enlaces publicos de empresa. Campos: `company_id`, `type`, `link`, `status`.

Relaciones: pertenece a `companies`.

### business_industries

Rubros configurables por empresa. Campos: `company_id`, `slug`, `name`, `description` y `status`.

Uso: define un set base de módulos sugeridos para el tipo de negocio, por ejemplo gimnasio, restaurante o retail. No reemplaza la personalización manual por empresa; sirve como punto de partida operativo.

Relaciones: pertenece a `companies`; se asocia a `companies.business_industry_id` como rubro activo.

### business_industry_module_sets

Set base de módulos por rubro. Campos: `company_id`, `business_industry_id`, `sub_section_id`, `is_enabled_by_default`, `reason` y `status`.

Uso: permite activar o desactivar módulos de forma masiva según el rubro de la empresa. Ejemplo: un rubro restaurante puede sugerir recetas y POS restaurante, mientras un rubro gimnasio puede priorizar membresías y asistencias.

Relaciones: pertenece a empresa, rubro y `sub_sections`. La aplicación del rubro actualiza `companies_sub_sections` e invalida la caché del menú de la empresa.

### menu_categories

Categorías visuales de primer nivel. Campos: `slug`, `name`, `order`, `status` y timestamps.

Relaciones: tiene muchas `sections`. La definición vigente reside en la base de datos y se administra junto con `sections`, `menu_groups` y `sub_sections`.

### sections

Secciones de menú principal. Campos: `menu_category_id`, `slug`, `name`, `order`, `dom_id`, `dom_label`, `dom_icon`, `has_sub_menu`, `status`.

Relaciones: pertenece a `menu_categories`; tiene `menu_groups` y `sub_sections`.

### menu_groups

Agrupaciones visuales opcionales dentro de una sección. Campos: `section_id`, `slug`, `name`, `order`, `status` y timestamps.

Relaciones: pertenece a `sections`; agrupa `sub_sections`. No concede permisos por sí misma.

### sub_sections

Ítems de menú. Campos: `section_id`, `menu_group_id`, `slug`, `name`, `description`, `order`, `dom_id`, `dom_label`, `dom_icon`, `dom_route`, `status`.

`description` contiene un resumen breve del propósito del acceso. Home lo usa para dar contexto, ampliar la búsqueda local y diferenciar módulos con nombres similares.

Relaciones: pertenece a `sections` y opcionalmente a `menu_groups`; se habilita por empresa mediante `companies_sub_sections`.

### companies_sub_sections

Permite activar, desactivar y ordenar opciones para una empresa. Campos: `company_id`, `sub_section_id`, `section_order`, `sub_section_order`, `status`.

Relaciones: une `companies` con `sub_sections`.

### roles

Roles internos por empresa. Campos: `company_id`, `slug`, `name`, `is_full_access`, `branch_scope_mode`, `cash_register_scope_mode`, `warehouse_scope_mode`, `status` y auditoría.

`is_full_access` indica que el perfil puede ingresar a todos los modulos habilitados para la empresa. El rol administrador inicial se crea con este valor activo.

Relaciones: usado por `users`; tiene permisos por módulo/acción mediante `role_sub_sections` y alcances mediante `role_branches`, `role_cash_registers` y `role_warehouses`.

### role_sub_sections

Permisos de módulo por rol. Campos: `company_id`, `role_id`, `sub_section_id`, `actions`, `status` y auditoría. `actions` es JSON y admite `view`, `create`, `update`, `delete`, `export`, `import` y `operate`.

Relaciones: une `roles` con `sub_sections`. La combinacion `role_id + sub_section_id` es unica para evitar duplicidad de permisos activos o inactivos sobre el mismo modulo.

Reglas:

- Solo se usa cuando `roles.is_full_access` es falso.
- Define visibilidad de menú y acceso backend por módulo y acción.
- `actions = null` equivale a todas las acciones para conservar perfiles anteriores.
- Se cachea por empresa y rol mediante `RolePermissionService`.
- Al cambiar un permiso se invalida el menu cacheado de ese rol.

### users

Además de los datos del colaborador, `users.session_version` funciona como contador de revocación. Cambiar contraseña o inactivar la cuenta incrementa este valor, elimina `remember_token` y revoca tokens personales; la siguiente solicitud invalida cualquier sesión con una versión anterior.

Usuarios internos del sistema. Campos: `company_id`, `role_id`, `branch_scope_mode`, `cash_register_scope_mode`, `warehouse_scope_mode`, `identity_document_type_id`, documento, nombre, email, password, teléfono, género, nacimiento y `status`.

Relaciones: pertenece a empresa, rol y tipo de documento. Puede vender, crear registros, recibir activos y tener preferencias. Los modos `inherit/restricted` determinan si hereda el alcance del perfil o lo reduce.

### authentication_events

Historial de autenticación por tenant y empresa. Campos: `company_id`, `user_id`, `tenant_slug`, `event_type`, `result`, `email`, `ip_address`, `user_agent`, `session_hash`, `reason` y `occurred_at`.

Relaciones: pertenece a `companies` y opcionalmente a `users`. Los intentos fallidos pueden no resolver un usuario. `session_hash` es SHA-256 del ID de sesión y nunca contiene el valor reutilizable. La tabla sirve para auditoría de accesos, bloqueos, cierres y revocaciones.

### role_branches, role_cash_registers y role_warehouses

Alcances operativos del perfil. Cada tabla guarda `company_id`, `role_id`, el recurso correspondiente, `status` y auditoría. Solo se consultan cuando el modo del perfil es `restricted`.

### user_branches, user_cash_registers y user_warehouses

Restricciones adicionales por colaborador. Cada tabla guarda `company_id`, `user_id`, el recurso correspondiente, `status` y auditoría. Una ausencia de filas con modo `inherit` conserva el alcance del perfil; no significa acceso total.

El alcance efectivo es la intersección del perfil y el colaborador. Cajas y almacenes también se intersectan con las sucursales efectivas.

### user_preferences

Preferencias por usuario. Campos: `user_id`, `slug`, `value`, `status`.

Relaciones: pertenece a `users`.

Uso actual en Home:

- Slug `config_companies_sub_sections`.
- `value` guarda JSON con preferencias globales y configuración por `sub_section_id`.
- Home permite modificar únicamente `show_only_favorites` e `is_favorite`.
- `show_actions` se conserva por compatibilidad y Home lo envía como `false`.
- `visible_in_menu` es un dato heredado que Home ya no modifica.
- La aplicación consolida una sola preferencia activa por usuario y slug al actualizar.
- Preferencias activas antiguas duplicadas pasan a `inactive`.

## Organizacion fisica

### branches

Sucursales de empresa. Campos: `company_id`, `internal_code`, `name`, direccion, referencia, telefono, email, capacidad, mapa, `status`.

Relaciones: pertenece a empresa; tiene series, almacenes, asistencias, membresias, dispositivos y activos.

### series

Series/correlativos por sucursal y tipo de documento. Campos: `branch_id`, `document_type_id`, `code`, `number`, `init`, `status`.

La asignación del correlativo bloquea la serie dentro de la transacción. `sales_header` garantiza que `company_id + serie_id + sequential` no se repita.

### series_correlative_movements

Bitácora inmutable de correlativos de venta. Campos: `company_id`, `serie_id`, `sale_header_id`, `user_id`, `sequential`, `action`, `source`, `note`, `metadata`, `occurred_at`.

Relaciones: pertenece a empresa, serie, venta y usuario. `action` distingue emisión y anulación; `source` distingue venta normal y POS. Anular una venta registra un nuevo evento y no libera el correlativo.

Relaciones: pertenece a sucursal y tipo de documento; usada por ventas.

## Catálogo comercial

### brands

Marcas comerciales propias de cada empresa. Campos: `company_id`, `internal_code`, `name`, `description`, `status` y auditoría.

Relaciones: pertenece a `companies`; tiene muchos productos mediante `items.brand_id`.

Reglas backend: `company_id + internal_code` y `company_id + name` se validan como únicos mediante `UniqueInCompany`. La tabla no declara restricciones únicas ni índices compuestos adicionales para estos campos.

### categories

Categorias comerciales. Campos: `company_id`, `internal_code`, `name`, `description`, `status`.

Relaciones: pertenece a empresa; se une a items por `category_items`.

### items

Productos, servicios y membresias de catalogo. Campos: `company_id`, `brand_id`, `currency_id`, `internal_code`, `barcode`, `name`, `description`, `price`, `price_includes_tax`, `igv_exempt`, `min_price`, `max_price`, `type`, `duration_type`, `duration_value`, `estimated_duration_minutes`, `capacity_control_enabled`, `capacity_limit`, `capacity_used`, `expires_at`, `see_my_web`, `see_my_web_price`, `status`.

`barcode` almacena un EAN-13 opcional a nivel de tabla. No declara un índice único ni un índice compuesto adicional; la unicidad por empresa es una regla de negocio validada en backend mediante `UniqueInCompany`. El módulo Productos lo exige para nuevos productos; Servicios y Membresías no usan código de barras, marca ni inventario físico.

`capacity_control_enabled`, `capacity_limit` y `capacity_used` controlan cupos comerciales para ítems no inventariables, principalmente servicios y membresías. Si el control está desactivado, el ítem se considera ilimitado y `capacity_limit` queda nulo. `expires_at` es opcional para todos los tipos; cuando vence, el backend inactiva el ítem en listados y lo bloquea en ventas.

`see_my_web` controla la publicación del item en el catálogo comercial y `see_my_web_price` controla si también se expone el precio.

Relaciones: pertenece a empresa, moneda y opcionalmente marca; tiene categorias; usado por ventas, stock y portal publico. `brand_id` usa `ON DELETE SET NULL`.

### category_items

Relacion entre categorias e items. Campos: `category_id`, `item_id`, `status`.

Relaciones: une `categories` con `items`.

Restricción: la combinación `category_id + item_id` es única.

### recipe_dishes

Cabecera de receta o platillo. Campos: `company_id`, `item_id`, `yield_quantity`, `waste_percentage`, `preparation_notes`, `status` y auditoria.

Relaciones: pertenece a `companies` y a `items`. Tiene insumos base, toppings y sabores. El `item_id` sigue representando el producto/servicio vendible; la receta es la capa operativa.

### recipe_dish_components

Insumos base consumidos por una receta. Campos: `recipe_dish_id`, `item_id`, `quantity`, `waste_percentage`, `note`, `status` y auditoria.

Relaciones: pertenece a `recipe_dishes` y usa `items` como insumo.

### recipe_toppings / recipe_dish_toppings / recipe_topping_components

`recipe_toppings` define extras o toppings con precio, moneda y estado. `recipe_dish_toppings` los habilita por platillo y define cantidades minimas/maximas. `recipe_topping_components` registra insumos propios del extra.

Relaciones: permiten que un adicional vendido tenga costo comercial y consumo operativo propio.

### recipe_dish_options / recipe_dish_option_components

Opciones o sabores de un platillo. Sirven para casos como pizzas de varios sabores, donde el producto base existe pero cada sabor consume insumos adicionales.

Relaciones: cada opcion pertenece a `recipe_dishes` y cada componente usa `items`.

### recipe_waste_records

Merma real registrada durante preparación o cierre operativo. Campos: `company_id`, `recipe_dish_id`, `warehouse_id`, `item_id`, `inventory_movement_id`, `quantity`, `unit_cost`, `total_cost`, `reason`, `occurred_at` y auditoría.

Relaciones: pertenece a receta opcional, almacén, insumo, movimiento de inventario y usuario responsable. El movimiento `recipe_waste` es obligatorio y conserva la salida física; por ello el registro no puede existir sin trazabilidad de Kardex.

## Clientes, membresias y asistencias

### customers

Clientes de la empresa. Campos: `company_id`, `identity_document_type_id`, `document_number`, `name`, `email`, `phone_number`, `gender`, `birthdate`, `status`.

Relaciones: pertenece a empresa y tipo de documento; tiene ventas, membresias, asistencias y huellas.

### subscriptions

Membresias reales de clientes. Campos: `company_id`, `branch_id`, `sale_header_id`, `sale_body_id`, `renewed_from_id`, `customer_id`, `duration_type`, `duration_value`, `start_date`, `end_date`, `set_end_of_day`, `force`, `attendance_limit_per_day`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente; puede venir de venta, renovación o alta manual directa. Cuando una venta incluye una membresía, el detalle puede indicar un `customer_id` beneficiario diferente al titular de la venta; si no se informa, se usa el titular.

### attendances

Registros de asistencia de clientes. Campos: `company_id`, `branch_id`, `customer_id`, `start_date`, `end_date`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente.

La asistencia activa es única por empresa, sucursal y cliente. El límite diario procede de la membresía y considera asistencias finalizadas del mismo día y sucursal. `status` incluye `absent` para cierres técnicos de asistencias que quedaron abiertas sin salida del cliente.

### user_attendances

Jornadas laborales de colaboradores. Campos: `company_id`, `branch_id`, `user_id`, `work_date`, `checked_in_at`, `checked_out_at`, `worked_minutes`, `source_type`, `source_reference`, `observation`, `motive`, `status` y auditoría.

Relaciones: pertenece a empresa, sucursal y usuario. La jornada activa es única por empresa y colaborador para impedir trabajo simultáneo en varias sedes. Los reportes semanales suman `worked_minutes` de jornadas finalizadas.

### user_biometric_fingerprints

Identidad biométrica de colaboradores. Campos: `company_id`, `user_id`, `biometric_device_id`, `device_user_id`, `finger_index`, `fingerprint_template`, `description`, `status` y auditoría.

Relaciones: pertenece a empresa, colaborador y dispositivo. El servicio biométrico reserva `device_user_id` considerando tanto clientes como colaboradores para evitar que un dispositivo asigne la misma identidad a dos personas.

### subscription_emails

Emails relacionados a membresias. Campos: `to`, `subject`, `body`, `extras_json`, `type`, `model_id`, `model_type`, `status`.

Relaciones: puede referenciar modelos mediante `model_id`/`model_type`. Los tipos iniciales son `SubscriptionExpired` y `SubscriptionWelcome`; este último se crea al agregar una membresía manual con correo de agradecimiento activo o cuando una venta genera una membresía y `company_settings.subscriptions.send_welcome_email_on_sale` está activo.

### loyalty_point_rules

Reglas de puntos por empresa. Campos: `company_id`, `name`, `description`, `trigger_type`, `apply_scope`, `amount_step`, `points_per_amount`, `points_per_unit`, `minimum_sale_total`, `starts_at`, `ends_at`, `status` y auditoría.

Uso: define si una venta otorga puntos por monto total, cantidad de ítems o venta de membresía. `apply_scope` permite reglas globales, solo productos, solo servicios, solo membresías o una selección concreta de ítems mediante `loyalty_point_rule_items`.

### loyalty_point_rule_items

Detalle opcional de ítems a los que aplica una regla de puntos. Campos: `company_id`, `loyalty_point_rule_id`, `item_id`, `status` y auditoría.

Relaciones: une reglas con productos, servicios o membresías del catálogo.

### customer_point_balances

Saldo materializado de puntos por cliente. Campos: `company_id`, `customer_id`, `points_balance` y auditoría.

Uso: permite consultar rápidamente los puntos vigentes sin recalcular todos los movimientos históricos.

### customer_point_movements

Kardex de puntos del cliente. Campos: `company_id`, `customer_id`, `sale_header_id`, `loyalty_point_rule_id`, `movement_type`, `points`, `reason`, `status` y auditoría.

Uso: conserva los puntos otorgados por venta y las reversas por anulación. Un movimiento no se elimina; si una venta se anula y la política lo permite, se registra un movimiento negativo asociado.

## Operaciones y servicios

### service_floors

Pisos o zonas operativas por sucursal. Campos: `company_id`, `branch_id`, `code`, `name`, `level_number`, `sort_order`, `background_color`, `description`, `status` y auditoría.

Relaciones: pertenece a empresa y sucursal; contiene muchas estaciones. `level_number` permite sótanos, planta baja o varios niveles, mientras `sort_order` controla el orden visual sin depender del nombre. El color de fondo es una referencia del plano y no modifica estados operativos.

### service_stations

Estaciones físicas donde ocurre una atención. Campos: `company_id`, `branch_id`, `service_floor_id`, `code`, `name`, `station_type`, `capacity`, `position_x`, `position_y`, `color`, `shape`, `description`, `status` y auditoría.

`station_type` admite mesa, sillón, cabina, habitación, cancha, bahía u otro. Así, la misma estructura sirve para restaurantes, barberías, clínicas, talleres, alquileres y centros deportivos. Una estación se considera ocupada cuando posee una sesión `pending` o `in_progress`; la disponibilidad no se almacena como un estado duplicado.

`position_x` y `position_y` guardan porcentajes entre 5 y 95 para mantener el plano responsive. `color` y `shape` dan una referencia visual estable, pero no reemplazan el indicador de disponibilidad. Un piso asignado debe pertenecer a la misma empresa y sucursal que la estación.

### service_sessions

Cabecera de una atención operativa. Campos: `company_id`, `branch_id`, `service_station_id`, `customer_id`, `assigned_user_id`, `sale_header_id`, responsables de apertura/cierre, `reference`, `session_type`, `status`, inicio, fin, duración, observación, cancelación y auditoría.

Relaciones: pertenece a empresa, sucursal, estación opcional, cliente opcional, colaborador responsable y venta opcional. Una estación no puede tener dos sesiones activas. El alcance de sucursal se valida también contra los permisos del colaborador autenticado.

### service_session_items

Productos o servicios atendidos dentro de una sesión. Campos: `company_id`, `service_session_id`, `item_id`, `assigned_user_id`, nombre y tipo históricos, cantidad, precio unitario, estado, inicio, fin, duración, observación y auditoría.

Cada detalle puede tener un responsable y cronómetro independiente. Iniciar un detalle inicia también la sesión si aún estaba pendiente. Finalizar un detalle no cierra automáticamente la sesión, porque puede quedar trabajo adicional o un cobro pendiente.

Reglas de integración:

- Restaurante POS usa estaciones tipo `table` y sesiones tipo `restaurant`.
- Servicios en curso usa sesiones tipo `catalog_service`, con o sin estación física.
- “Cobrar en POS” envía `service_session_id`; la sesión se vincula y finaliza dentro de la misma transacción que crea `sales_header`.
- Una sesión ya finalizada no puede cobrarse nuevamente.
- `duration_minutes` se consolida al terminar para conservar indicadores históricos estables.

## Finanzas

### cash_registers

Cajas configuradas por sucursal. Campos principales: `company_id`, `branch_id`, `code`, `name`, `is_main`, `status` y auditoria.

`is_main` identifica la caja principal de la sucursal. Una caja principal no debe cerrarse mientras existan cajas secundarias abiertas en la misma sucursal, porque su cierre representa el cuadre general de la operación.

Al registrar una caja marcada como principal, las demás cajas de la misma sucursal quedan como secundarias para mantener una única caja principal operativa.

### cash_session_inventory_counts

Conteo físico de inventario asociado al cierre de caja principal. Campos: `company_id`, `branch_id`, `cash_session_id`, `warehouse_id`, `item_id`, `inventory_movement_id`, `system_quantity`, `counted_quantity`, `difference_quantity`, `observation`, `status` y auditoria.

Uso: cuando la cantidad real difiere del saldo del sistema, debe crearse un movimiento de inventario con origen `physical_count` y vincularlo en `inventory_movement_id`. Esto mantiene trazabilidad de mermas, diferencias de cocina y ajustes de cierre.

### taxes

Tributos configurables por empresa. Campos: `company_id`, `code`, `name`, `description`, `rate`, `calculation_type`, `operation_type`, `min_apply_quantity`, `max_apply_quantity`, `scope`, `is_required`, `is_default` y `status`.

Relaciones: pertenece a `companies`. `scope` separa venta y compra; si un tributo aplica a ambos ámbitos se registra una fila por cada alcance para mantener trazabilidad independiente. `min_apply_quantity` y `max_apply_quantity` aplican principalmente a tributos fijos opcionales como ICBP, donde el usuario puede indicar cuántas veces se cobra.
### sale_taxes / purchase_taxes

Foto histórica del tributo aplicado al documento. Guardan `name`, `description`, `rate`, `calculation_type`, `operation_type`, `is_required`, `quantity`, `base_amount` y `amount` para que ventas y compras mantengan trazabilidad aunque luego cambie la configuración de `taxes`. `quantity` es entero y se usa principalmente en tributos fijos opcionales como `ICBP`.

### payment_methods

Métodos de pago generales configurables por empresa y alcance. Campos: `company_id`, `code`, `name`, `category`, `sunat_code`, `description`, `image_path`, `scope`, `requires_reference`, `supports_variants`, `allows_partial_payment`, `is_default` y `status`.

Relaciones: pertenece a `companies` y tiene variantes activas en `payment_method_variants`. `sunat_code` conserva la referencia SUNAT cuando exista y `image_path` almacena la ruta pública generada por backend dentro del tenant. `scope` define si el método aplica a ventas, compras o ambos.

Nota vigente: `YAPE`, `PLIN`, `AGORA_PAY`, `BIM` e `IZIPAYYA` no son métodos generales; son variantes de `DIGITAL_WALLET`. Por eso no deben existir como códigos raíz activos en `payment_methods`.

### payment_method_variants

Opciones específicas de un método de pago. Campos: `company_id`, `payment_method_id`, `code`, `name`, `sunat_code`, `image_path`, `description`, `requires_reference`, `is_default` y `status`.

Uso: permite mostrar al usuario el método general y la opción concreta sin duplicar lógica. Ejemplos: `Billetera digital -> Yape`, `Tarjeta de crédito -> Visa crédito`.

### sale_payments / purchase_payments

Foto histórica de los pagos del documento. Guardan método general, variante opcional, nombre, monto, referencia y nota.

Nota vigente: estas tablas conservan `payment_method_id`, `payment_method_variant_id`, nombre histórico, monto, referencia y nota para que ventas y compras mantengan trazabilidad aunque luego cambie la configuración de `payment_methods` o `payment_method_variants`.

### sale_accounts_receivable / purchase_accounts_payable

Cuentas por cobrar y cuentas por pagar generadas cuando un documento queda con saldo pendiente por `cash_on_delivery` o `installments`. Campos principales: `company_id`, documento origen, tercero, moneda, fechas, `payment_modality`, `original_amount`, `extra_percentage`, `extra_amount`, `total_amount`, `paid_amount`, `pending_amount` y `status`.

### sale_receivable_installments / purchase_payable_installments

Cuotas derivadas de una cuenta. Guardan número de cuota, fecha de vencimiento, monto, pagado, pendiente y estado.

### sale_receivable_payments / purchase_payable_payments

Abonos posteriores o iniciales aplicados a cuentas por cobrar/pagar. Guardan método general, variante, importe, referencia, observación y responsable.
### misc_expense_categories

Categorías de gastos varios por empresa. Campos: `company_id`, `name`, `description` y `status`.

Uso: clasifica egresos operativos que no representan una compra de inventario, como reparaciones, mantenimiento, suministros menores o servicios básicos.

### misc_expenses

Gastos varios. Campos: `company_id`, `branch_id`, `cash_session_id`, `payment_method_id`, `currency_id`, `misc_expense_category_id`, `responsible_user_id`, `expense_date`, `reference`, `concept`, `amount`, `description`, `observation` y `status`.

Uso: registra egresos financieros no asociados a mercadería. Si se vincula a una caja abierta, crea un movimiento de caja de tipo `expense` para que el cierre y los estados financieros reflejen el gasto. La anulación cancela el gasto y el movimiento de caja relacionado.

## Ventas

### sales_header

Cabecera de venta. Campos: `serie_id`, `sequential`, `holder_id`, `seller_id`, `currency_id`, `warehouse_id`, `cash_session_id`, `quotation_header_id`, `issue_date`, `delivery_mode`, `delivery_status`, `delivered_at`, `delivered_by`, `delivery_observation`, `subtotal`, `tax`, `commission_total`, `total`, `observation`, `status`.

Relaciones: pertenece a serie, cliente comprador, vendedor, moneda, almacén, caja, cotización opcional y usuario que confirmó entrega; tiene detalles y, cuando aplica, un seguimiento en `sale_deliveries`.

Cuando `quotation_header_id` tiene valor, la venta se originó desde una cotización. Al concretarse, la cotización pasa a `converted` y conserva la referencia a la venta.

### sales_body

Detalle de venta. Campos: `sale_header_id`, `item_id`, `currency_id`, `name`, `quantity`, `price`, `price_includes_tax`, `igv_exempt`, `total`, `commission_type`, `commission_value`, `commission_amount`, `customer_id`, `type`, `observation`, `extras`, `status`.

La comision del detalle es una foto historica. `commission_type` admite `none`, `percentage` o `fixed`; `commission_amount` guarda el monto calculado y no modifica el total cobrado.

Relaciones: pertenece a cabecera, item, moneda y cliente.

### sale_deliveries

Cabecera operativa de entrega pendiente. Campos: `company_id`, `sale_header_id`, `warehouse_id`, `total_quantity`, `delivered_quantity`, `pending_quantity`, `status`, `last_delivered_at`, `last_delivered_by`, `observation` y auditoría.

Uso: se crea solo cuando una venta con productos contabilizables nace con `delivery_mode = pending`. En ese caso la venta queda registrada, pero no descuenta stock todavía. El descuento real ocurre cuando se registra una entrega parcial o total desde `Ventas > Entregas pendientes`.

Estados: `pending`, `partial`, `delivered` y `canceled`.

### sale_delivery_items

Detalle pendiente por producto vendido. Campos: `company_id`, `sale_delivery_id`, `sale_body_id`, `item_id`, `quantity_ordered`, `quantity_delivered`, `quantity_pending`, `status` y auditoría.

Uso: permite entregar una venta por partes. Cada línea conserva cuánto se vendió, cuánto ya salió físicamente del almacén y cuánto queda pendiente.

### sale_delivery_events

Historial de cada acto de entrega. Campos: `company_id`, `sale_delivery_id`, `warehouse_id`, `delivered_by`, `delivered_at`, `total_quantity`, `observation` y `status`.

Uso: registra quién entregó, desde qué almacén y cuándo. Es la cabecera de trazabilidad para auditoría y reportes.

### sale_delivery_event_items

Detalle de productos entregados en un evento. Campos: `company_id`, `sale_delivery_event_id`, `sale_delivery_item_id`, `sale_body_id`, `item_id`, `inventory_movement_id`, `quantity` y `created_at`.

Uso: conecta la entrega con el movimiento de inventario generado. `inventory_movement_id` puede ser nulo cuando la salida corresponde a una composición o lógica que no genera movimiento directo del producto vendido.

### quotation_headers

Cabecera de cotización. Campos: `company_id`, `branch_id`, `holder_id`, `seller_id`, `currency_id`, `sale_header_id`, `reference`, `issue_date`, `valid_until`, `subtotal`, `tax`, `total`, `observation` y `status`.

Uso: registra propuestas comerciales antes de convertirse en venta. Desde nueva venta se puede jalar una cotización; el backend recalcula los precios vigentes del catálogo antes de armar el borrador de venta.

Estados: `draft`, `sent`, `accepted`, `converted`, `canceled` y `expired`.

### quotation_items

Detalle histórico de la cotización. Guarda `item_id`, `currency_id`, `name`, `type`, `quantity`, `price`, `price_includes_tax`, `igv_exempt`, `total` y `observation`.

El precio guardado es la foto de la propuesta. Al convertir a venta se compara contra el precio vigente y se marca si fue recalculado.

### quotation_taxes

Foto histórica de tributos aplicados a la cotización. Conserva nombre, descripción, tasa, tipo de cálculo, tipo de operación, obligatoriedad, cantidad, base y monto.

## Inventario

### warehouses

Almacenes por sucursal. Campos: `branch_id`, `name`, `status`.

Relaciones: pertenece a sucursal; tiene `warehouse_items`.

### warehouse_items

Stock por item en almacen. Campos: `warehouse_id`, `item_id`, `quantity`, `minimum_stock`, `average_cost`, `inventory_value`, `status`.

Relaciones: une almacenes con items. La combinacion `warehouse_id + item_id` es unica. `minimum_stock` define la alerta especifica para cada almacen.

`quantity` es un saldo materializado para consultas rápidas. No debe modificarse directamente desde controladores o módulos: toda variación pasa por `InventoryMovementService`.

### inventory_movements

Kardex inmutable de productos por almacén. Campos principales: `company_id`, `warehouse_id`, `item_id`, `user_id`, `movement_type`, `origin_type`, `origin_id`, `quantity_before`, `quantity_change`, `quantity_after`, `unit_cost`, `value_before`, `value_change`, `value_after`, `reason`, `metadata` y `created_at`.

`movement_type` admite `entry`, `exit` y `correction`. `origin_type` integra productos, ventas, compras, anulaciones, traslados y operaciones manuales sin cambiar la tabla. `origin_id` referencia lógicamente el registro que produjo el movimiento; no usa clave foránea porque puede apuntar a entidades de módulos distintos.

Relaciones: pertenece a empresa, almacén, producto y opcionalmente usuario. Sus índices por empresa/fecha, almacén/producto/fecha y origen mejoran consultas; no representan reglas de unicidad.

Reglas:

- Guarda saldo anterior, variación firmada y saldo resultante.
- No se edita ni elimina como parte del flujo operativo.
- Una anulación o corrección crea un nuevo movimiento compensatorio.
- `warehouse_items.quantity` debe coincidir con el último `quantity_after` del almacén y producto.
- Los traslados generan una salida `transfer_out` y una entrada `transfer_in`; la referencia compartida se guarda en `metadata.reference` y se expone como atributo `reference` del modelo.

## Compras

### suppliers

Proveedores por empresa. Campos: `company_id`, documento, nombre, contacto, teléfono, correo, dirección, `status` y auditoría.

### purchase_headers

Cabecera de orden o factura de compra. Relaciona empresa, proveedor, almacén y moneda; guarda documento, fechas, `delivery_mode`, totales, observación y estado.

`delivery_mode = immediate` registra la recepción total al crear la compra y genera movimientos de inventario. `delivery_mode = pending` deja la compra pendiente para recepciones parciales o totales posteriores.
### purchase_items

Detalle solicitado. Guarda producto, nombre histórico, cantidad, cantidad recibida, costo unitario, subtotal y estado de recepción.

### purchase_receipts

Evento físico de recepción. Una compra puede tener varias recepciones parciales. Guarda almacén, referencia, fecha, observación y responsable.

### purchase_receipt_items

Detalle recibido. Relaciona el detalle de compra, producto y `inventory_movement_id`; conserva cantidad, costo unitario y costo total.

Reglas:

- Registrar una compra con `delivery_mode = pending` no cambia existencias. Con `delivery_mode = immediate`, se registra la recepción total y se actualiza el inventario en la misma transacción.
- Cada detalle recibido genera una entrada `purchase`.
- La recepción y sus movimientos se confirman en una sola transacción.
- El costo actualiza el promedio ponderado por producto y almacén.
- Una compra con recepción no se anula; se compensa mediante devolución a proveedor.

## Activos

### asset_categories

Catálogo de clasificación de activos por empresa. Campos: `company_id`, `name`, `description`, `status`.

Relaciones: pertenece a empresa y clasifica registros de `assets`. Al eliminar una categoría, los activos se conservan y quedan sin categoría.

### assets

Catálogo de activos. Campos: `company_id`, `asset_category_id`, `internal_code`, `patrimonial_code`, `serial_number`, `name`, `description`, `management_type`, `status`.

Relaciones: pertenece a empresa y opcionalmente a `asset_categories`; se asigna a sucursales. `internal_code` identifica el registro en la plataforma, `patrimonial_code` corresponde al control patrimonial de la empresa y `serial_number` a la serie física del fabricante.

### branch_assets

Activos disponibles/asignados en sucursales. Campos: `branch_id`, `asset_id`, `currency_id`, `quantity`, `acquisition_value`, `acquisition_date`, `note`, `status`.

Relaciones: pertenece a sucursal, activo y moneda.

### asset_assignments

Asignaciones de activos a usuarios. Campos: `user_id`, `branch_id`, `asset_id`, `currency_id`, `quantity`, `acquisition_value`, `acquisition_date`, `note`, `status`.

Relaciones: pertenece a usuario, sucursal, activo y moneda.

### asset_assignment_logs

Historial de movimientos de activos. Campos: `asset_assignment_id`, `from_user_id`, `to_user_id`, `action_type`, `quantity`, `note`.

Relaciones: referencia asignaciones y usuarios origen/destino.

## Libro de reclamaciones

### book_complaints

Reclamos, quejas o sugerencias. Campos: `company_id`, `branch_id`, `identity_document_type_id`, `document_number`, `name`, `email`, `phone_number`, `type`, `description`, `request`, `evidence`, `admin_response`, datos de dispositivo/IP, `status`. `branch_id` es obligatorio para ubicar la atención por sucursal desde Guest y System.

Relaciones: pertenece a empresa, sucursal y tipo de documento.

## Biometría

### biometric_device_brands

Marcas de dispositivos biométricos por empresa. Campos: `company_id`, `slug`, `name`, `description`, `status` y auditoría.

Relaciones: pertenece a empresa y tiene muchos modelos.

### biometric_device_models

Modelos de dispositivos biométricos por marca. Campos: `company_id`, `biometric_device_brand_id`, `slug`, `name`, `description`, `status` y auditoría.

Relaciones: pertenece a empresa y marca; tiene muchos dispositivos.

### biometric_devices

Dispositivos biométricos por empresa/sucursal. Campos: `company_id`, `branch_id`, `biometric_device_model_id`, `name`, `serial_number`, `ip_address`, `port`, `device_id`, `description` y `status`.

Relaciones: pertenece a empresa, sucursal y modelo; por el modelo se obtiene la marca. Tiene huellas de clientes.

Nota vigente: al crear un dispositivo sin estado explicito se registra como `active`. No genera un activo patrimonial automaticamente hasta que se defina esa politica por empresa.

### customer_biometric_fingerprints

Asociación cliente-huella-dispositivo. Campos: `company_id`, `customer_id`, `biometric_device_id`, `device_user_id`, `finger_index`, `fingerprint_template`, `description`, `status`.

Relaciones: pertenece a empresa, cliente y dispositivo.

## Trazabilidad y operaciones ampliadas

### business_audit_logs

Bitácora transversal de cambios sensibles. Relaciona empresa, sucursal y usuario; registra módulo, acción, modelo, registro, resumen, estado anterior/nuevo y contexto técnico. Nunca almacena contraseñas, tokens, secretos ni plantillas biométricas.

### book_complaint_attachments / book_complaint_status_histories

Conservan adjuntos múltiples y cada transición del libro de reclamaciones. `book_complaints.tracking_code` permite consulta pública aislada; `public_response` se separa de `admin_response`.

### biometric_device_events

Recibe eventos firmados por dispositivo. `event_uuid` hace idempotente cada evento; `processing_status`, `attempts`, `last_error` y `processed_at` permiten reintento y diagnóstico sin duplicar asistencias.

### user_work_schedules / user_attendance_breaks / user_attendance_corrections

Modelan horarios por empresa, sucursal o usuario, pausas múltiples y correcciones con aprobación. `user_attendances` conserva métricas históricas ordinarias, tardanza, horas extra y descanso.

### service_session_events / service_session_pauses

Registran línea de tiempo, reasignaciones, cancelaciones y pausas de una atención. Los eventos son append-only y mantienen actor, estado, nota y metadatos.

### supplier_contacts / supplier_bank_accounts

Separan contactos y cuentas bancarias múltiples del proveedor. Cada fila conserva empresa, estado y marca de principal.

### purchase_expenses

Gastos adicionales de compra como flete o seguro, con importe y método de distribución.

### purchase_returns / purchase_return_items

Devoluciones al proveedor vinculadas con compra, recepción, almacén, detalles y movimientos de inventario.

### attendance_corrections

Auditoría de correcciones de asistencia de clientes. Conserva fechas anteriores, fechas solicitadas, motivo, solicitante, decisión, revisor y fecha de revisión.

### inventory_stock_alerts

Estado persistente de stock bajo por `warehouse_item`. Solo mantiene una alerta abierta por saldo y registra cuándo se resolvió y quién produjo la recuperación cuando corresponde.

### inventory_guides / inventory_guide_items

Cabecera y detalle de guías de entrada/salida. Cada detalle enlaza el movimiento de inventario que materializó la operación.

### purchase_headers / purchase_items

Además del documento y recepción, conservan referencia interna, entrega, aprobación, gastos, saldo y estado de pago. Cada detalle separa costo original, gasto distribuido y costo unitario de inventario.

### book_complaint_attachments / book_complaint_status_histories

Evidencias múltiples e historial inmutable de estados del libro de reclamaciones.

## Criterio de migraciones

- Separar estructura y datos iniciales: las migraciones de creación definen tablas, claves primarias y claves foráneas; los inserts iniciales viven en una migración dedicada.
- Separar por dominio cuando una migración crezca: maestros, empresas, catálogo, caja, inventario, ventas y compras deben poder evolucionar sin mezclar reglas de negocio ajenas.
- Toda tabla operativa, hija o maestro configurable debe tener `company_id` requerido cuando el dato pertenece a una empresa. Las filas hijas deben poblarlo desde la cabecera, el almacén, la sucursal o el usuario autenticado antes de guardar. Los catálogos base como documentos y monedas también se tratan como configurables por empresa.
- La caché de maestros configurables se invalida por `company_id`; no debe usarse `Cache::flush()` para estos datos porque puede afectar menús, perfiles y otros initParams de empresas no relacionadas.
- Las reglas `unique(...)` deben incluir `company_id` cuando la unicidad sea por empresa. Excepciones: tablas globales del framework o catálogos maestros realmente compartidos.
- No crear índices explícitos salvo decisión justificada por consulta crítica. Las claves primarias, claves foráneas y `unique(...)` sí se mantienen porque expresan integridad.
- Los montos y cantidades usan `decimal(15, 3)` como estándar operativo cuando se requieran hasta 12 enteros. Las cadenas deben tener longitud explícita; usar `text`/`longText` cuando el contenido supere una cadena razonable.
- Evitar comentarios decorativos o símbolos en migraciones. Los comentarios sólo deben explicar una decisión técnica que no sea evidente.

## Estado de revisión

- Los servicios modificados en esta fase poblan `company_id` en almacenes, saldos, preferencias, ventas, compras, recetas, caja y activos.
- Las nuevas tablas declaran clave foránea hacia `companies` y sus relaciones operativas.
- La separación física de migraciones grandes se pospone hasta cerrar la fase reiniciable: mover bloques ahora cambiaría timestamps y dependencias sin aportar una mejora funcional.
- Las reglas únicas se mantienen solo cuando expresan identidad estructural, como correlativos, referencias internas o códigos de seguimiento.
