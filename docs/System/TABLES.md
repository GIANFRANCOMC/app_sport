# System - Tablas y relaciones

Este archivo describe las tablas creadas por migraciones y usadas por System. Algunas tablas tambien son leidas por Guest, pero la administracion principal pertenece a System.


## Landlord multi-tenant

Estas tablas viven en la conexión `landlord`, no en cada BD tenant. Resuelven exclusivamente subdominios registrados hacia una base de datos aislada.

### tenant_databases

Registro central de tenants. Campos: `slug`, `company_id`, `database_name`, `status` y `last_resolved_at`.

`company_id` es el ID raíz esperado dentro de la BD tenant. No declara FK porque `companies` pertenece a la base tenant, no a landlord.

No almacena credenciales, host ni puerto. La conexión usa exclusivamente configuración segura del servidor. `database_name` debe cumplir el prefijo y formato definidos en `config/tenancy.php`.

### tenant_domains

Subdominios asociados a cada tenant. Campos: `tenant_database_id`, `domain`, `type`, `is_primary` y `status`. `type` se conserva como `subdomain`; dominios personalizados no son resueltos por esta aplicación.

Relaciones: pertenece a `tenant_databases`. `domain` debe ser único para evitar que dos clientes resuelvan al mismo host.
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

Configuración extensible por empresa. Cada registro usa `company_id`, `group`, `key`, `value`, `description`, `value_type` y `status`. `description` explica el efecto operativo de la clave para que futuras interfaces administrativas puedan mostrar ayuda contextual. `value` puede ser nulo y `value_type` permite interpretarlo como `string`, `boolean`, `integer`, `decimal` o `json`.

El grupo `internal_code_prefixes` contiene las claves `product`, `service`, `subscription`, `brand`, `category`, `branch`, `asset` y `recipe`. Sus valores iniciales son `PRO`, `SER`, `MEM`, `MAR`, `CAT`, `SUC`, `ACT` y `REC`. Un valor nulo o vacío desactiva el prefijo.

El grupo `inventory` contiene `allow_negative_stock_on_sale`, booleano con valor predeterminado `false`. Cuando está desactivado, crear una venta normal o POS/caja se bloquea si la salida supera el stock disponible del almacén seleccionado. Cuando está activo, la venta puede dejar saldo negativo.

El grupo `inventory` también contiene `restore_stock_on_sale_cancellation`, booleano con valor predeterminado `false`. Cuando está desactivado, anular una venta no modifica existencias; una devolución física se registra posteriormente desde Inventario. Cuando está activo, la anulación repone automáticamente los productos en el almacén asociado a la venta.

El grupo `localization` contiene `timezone`, con valor inicial `America/Lima`. Debe almacenar una zona horaria IANA y se usa para construir límites diarios coherentes en Dashboard y procesos operativos.

El grupo `dashboard` contiene `membership_expiration_window_days`, entero con valor inicial `7`. Define cuántos días calendario, incluyendo la fecha consultada, abarca el KPI de membresías próximas a vencer.

La combinación `company_id + group + key` es única: una empresa no puede tener dos valores activos o históricos ambiguos para la misma configuración.

Relaciones: cada configuración pertenece a `companies` mediante `company_id`.

### company_socials_media

Redes sociales y enlaces publicos de empresa. Campos: `company_id`, `type`, `link`, `status`.

Relaciones: pertenece a `companies`.

### sections

Secciones de menu principal. Campos: `slug`, `name`, `order`, `dom_id`, `dom_label`, `dom_icon`, `has_sub_menu`, `status`.

Relaciones: tiene `sub_sections`.

### sub_sections

Items de menú. Campos: `section_id`, `slug`, `name`, `description`, `order`, `dom_id`, `dom_label`, `dom_icon`, `dom_route`, `status`.

`description` contiene un resumen breve del propósito del acceso. Home lo usa para dar contexto, ampliar la búsqueda local y diferenciar módulos con nombres similares.

Relaciones: pertenece a `sections`; se habilita por empresa mediante `companies_sub_sections`.

### companies_sub_sections

Permite activar/desactivar subsecciones para una empresa. Campos: `company_id`, `sub_section_id`, `status`.

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

Usuarios internos del sistema. Campos: `company_id`, `role_id`, `branch_scope_mode`, `cash_register_scope_mode`, `warehouse_scope_mode`, `identity_document_type_id`, documento, nombre, email, password, teléfono, género, nacimiento y `status`.

Relaciones: pertenece a empresa, rol y tipo de documento. Puede vender, crear registros, recibir activos y tener preferencias. Los modos `inherit/restricted` determinan si hereda el alcance del perfil o lo reduce.

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

Productos, servicios y membresias de catalogo. Campos: `company_id`, `brand_id`, `currency_id`, `internal_code`, `barcode`, `name`, `description`, `price`, `min_price`, `max_price`, `type`, `duration_type`, `duration_value`, `see_my_web`, `see_my_web_price`, `status`.

`barcode` almacena un EAN-13 opcional a nivel de tabla. No declara un índice único ni un índice compuesto adicional; la unicidad por empresa es una regla de negocio validada en backend mediante `UniqueInCompany`. El módulo Productos lo exige para nuevos productos. `see_my_web` controla la publicación del item en el catálogo comercial y `see_my_web_price` controla si también se expone el precio.

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

## Clientes, membresias y asistencias

### customers

Clientes de la empresa. Campos: `company_id`, `identity_document_type_id`, `document_number`, `name`, `email`, `phone_number`, `gender`, `birthdate`, `status`.

Relaciones: pertenece a empresa y tipo de documento; tiene ventas, membresias, asistencias y huellas.

### subscriptions

Membresias reales de clientes. Campos: `company_id`, `branch_id`, `sale_header_id`, `sale_body_id`, `customer_id`, `duration_type`, `duration_value`, `start_date`, `end_date`, `set_end_of_day`, `force`, `attendance_limit_per_day`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente; puede venir de venta.

### attendances

Registros de asistencia de clientes. Campos: `company_id`, `branch_id`, `customer_id`, `start_date`, `end_date`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente.

La asistencia activa es única por empresa, sucursal y cliente. El límite diario procede de la membresía y considera asistencias finalizadas del mismo día y sucursal.

### user_attendances

Jornadas laborales de colaboradores. Campos: `company_id`, `branch_id`, `user_id`, `work_date`, `checked_in_at`, `checked_out_at`, `worked_minutes`, `source_type`, `source_reference`, `observation`, `motive`, `status` y auditoría.

Relaciones: pertenece a empresa, sucursal y usuario. La jornada activa es única por empresa y colaborador para impedir trabajo simultáneo en varias sedes. Los reportes semanales suman `worked_minutes` de jornadas finalizadas.

### user_biometric_fingerprints

Identidad biométrica de colaboradores. Campos: `company_id`, `user_id`, `biometric_device_id`, `device_user_id`, `finger_index`, `fingerprint_template`, `description`, `status` y auditoría.

Relaciones: pertenece a empresa, colaborador y dispositivo. El servicio biométrico reserva `device_user_id` considerando tanto clientes como colaboradores para evitar que un dispositivo asigne la misma identidad a dos personas.

### subscription_emails

Emails relacionados a membresias. Campos: `to`, `subject`, `body`, `extras_json`, `type`, `model_id`, `model_type`, `status`.

Relaciones: puede referenciar modelos mediante `model_id`/`model_type`.

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

Métodos de pago configurables por empresa y alcance. Campos: `company_id`, `code`, `sunat_code`, `name`, `image_path`, `scope`, `requires_reference`, `is_default` y `status`.

Relaciones: pertenece a `companies`. `sunat_code` conserva la referencia SUNAT cuando exista y `image_path` permite mostrar una imagen o marca visual del método en futuras interfaces. `scope` define si el método aplica a ventas, compras o ambos.
### sale_payments / purchase_payments

Foto histórica de los pagos del documento. Guardan método, nombre, monto, referencia y nota.

## Ventas

### sales_header

Cabecera de venta. Campos: `serie_id`, `sequential`, `holder_id`, `seller_id`, `currency_id`, `issue_date`, `total`, `observation`, `status`.

Relaciones: pertenece a serie, cliente comprador, vendedor y moneda; tiene detalles.

### sales_body

Detalle de venta. Campos: `sale_header_id`, `item_id`, `currency_id`, `name`, `quantity`, `price`, `total`, `customer_id`, `type`, `observation`, `extras`, `status`.

Relaciones: pertenece a cabecera, item, moneda y cliente.

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

Reclamos, quejas o sugerencias. Campos: `company_id`, `branch_id`, `identity_document_type_id`, `document_number`, `name`, `email`, `phone_number`, `type`, `description`, `request`, `evidence`, `admin_response`, datos de dispositivo/IP, `status`.

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

## Criterio de migraciones

- Separar estructura y datos iniciales: las migraciones de creación definen tablas, claves primarias y claves foráneas; los inserts iniciales viven en una migración dedicada.
- Separar por dominio cuando una migración crezca: maestros, empresas, catálogo, caja, inventario, ventas y compras deben poder evolucionar sin mezclar reglas de negocio ajenas.
- Toda tabla operativa, hija o maestro configurable debe tener `company_id` requerido cuando el dato pertenece a una empresa. Las filas hijas deben poblarlo desde la cabecera, el almacén, la sucursal o el usuario autenticado antes de guardar. Los catálogos base como documentos y monedas también se tratan como configurables por empresa.
- La caché de maestros configurables se invalida por `company_id`; no debe usarse `Cache::flush()` para estos datos porque puede afectar menús, perfiles y otros initParams de empresas no relacionadas.
- Las reglas `unique(...)` deben incluir `company_id` cuando la unicidad sea por empresa. Excepciones: tablas globales del framework o catálogos maestros realmente compartidos.
- No crear índices explícitos salvo decisión justificada por consulta crítica. Las claves primarias, claves foráneas y `unique(...)` sí se mantienen porque expresan integridad.
- Los montos y cantidades usan `decimal(16, 4)` como estándar operativo. Las cadenas deben tener longitud explícita; usar `text`/`longText` cuando el contenido supere una cadena razonable.
- Evitar comentarios decorativos o símbolos en migraciones. Los comentarios sólo deben explicar una decisión técnica que no sea evidente.

## Pendientes y mejoras por realizar

- Completar el endurecimiento de `company_id` nullable a requerido en tablas heredadas cuando se confirme que todos los servicios lo poblan siempre.
- Revisar `unique(...)` por empresa en tablas donde la regla de negocio sea realmente estructural; evitar índices explícitos no justificados.
- Separar migraciones grandes por dominio cuando se cierre la fase reiniciable.
