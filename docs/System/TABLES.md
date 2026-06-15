# System - Tablas y relaciones

Este archivo describe las tablas creadas por migraciones y usadas por System. Algunas tablas tambien son leidas por Guest, pero la administracion principal pertenece a System.

## Maestros generales

### identity_document_types

Tipos de documento de identidad. Campos principales: `code`, `name`, `is_searchable`, `min_length`, `max_length`, `status`.

Relaciones: usado por `users`, `customers` y `book_complaints`.

### document_types

Tipos de documentos comerciales o comprobantes. Campos: `code`, `name`, `status`.

Relaciones: usado por `series`.

### currencies

Monedas. Campos: `code`, `sign`, `singular_name`, `plural_name`, `status`.

Relaciones: usado por empresas, items, ventas, activos y asignaciones.

## Empresa, menu y usuarios

### companies

Empresa o tenant funcional. Campos: `slug`, `internal_code`, documento, razon social, nombre comercial, moneda, tagline, descripcion, direccion, telefono, email, token externo, imagenes y `status`.

### `company_settings`

Configuración extensible por empresa. Cada registro usa `company_id`, `group`, `key`, `value`, `value_type` y `status`. `value` puede ser nulo y `value_type` permite interpretarlo como `string`, `boolean`, `integer`, `decimal` o `json`.

El grupo `internal_code_prefixes` contiene las claves `product`, `service`, `subscription`, `brand`, `category`, `branch` y `asset`. Sus valores iniciales son `PRO`, `SER`, `MEM`, `MAR`, `CAT`, `SUC` y `ACT`. Un valor nulo o vacío desactiva el prefijo.

El grupo `inventory` contiene `restore_stock_on_sale_cancellation`, booleano con valor predeterminado `false`. Cuando está desactivado, anular una venta no modifica existencias; una devolución física se registra posteriormente desde Inventario. Cuando está activo, la anulación repone automáticamente los productos en el almacén asociado a la venta.

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

Roles internos. Campos: `slug`, `name`, `status`.

Relaciones: usado por `users`.

### users

Usuarios internos del sistema. Campos: `company_id`, `role_id`, `identity_document_type_id`, documento, nombre, email, password, telefono, genero, nacimiento, `status`.

Relaciones: pertenece a empresa, rol y tipo de documento. Puede vender, crear registros, recibir activos y tener preferencias.

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

Relaciones: pertenece a sucursal y tipo de documento; usada por ventas.

## Catalogo comercial

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

## Clientes, membresias y asistencias

### customers

Clientes de la empresa. Campos: `company_id`, `identity_document_type_id`, `document_number`, `name`, `email`, `phone_number`, `gender`, `birthdate`, `status`.

Relaciones: pertenece a empresa y tipo de documento; tiene ventas, membresias, asistencias y huellas.

### subscriptions

Membresias reales de clientes. Campos: `company_id`, `branch_id`, `sale_header_id`, `sale_body_id`, `customer_id`, `duration_type`, `duration_value`, `start_date`, `end_date`, `set_end_of_day`, `force`, `attendance_limit_per_day`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente; puede venir de venta.

### attendances

Registros de asistencia. Campos: `company_id`, `branch_id`, `customer_id`, `start_date`, `end_date`, `observation`, `motive`, `type`, `status`.

Relaciones: pertenece a empresa, sucursal y cliente.

### subscription_emails

Emails relacionados a membresias. Campos: `to`, `subject`, `body`, `extras_json`, `type`, `model_id`, `model_type`, `status`.

Relaciones: puede referenciar modelos mediante `model_id`/`model_type`.

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

Cabecera de orden o factura de compra. Relaciona empresa, proveedor, almacén y moneda; guarda documento, fechas, totales, observación y estado.

### purchase_items

Detalle solicitado. Guarda producto, nombre histórico, cantidad, cantidad recibida, costo unitario, subtotal y estado de recepción.

### purchase_receipts

Evento físico de recepción. Una compra puede tener varias recepciones parciales. Guarda almacén, referencia, fecha, observación y responsable.

### purchase_receipt_items

Detalle recibido. Relaciona el detalle de compra, producto y `inventory_movement_id`; conserva cantidad, costo unitario y costo total.

Reglas:

- Registrar una compra no cambia existencias.
- Cada detalle recibido genera una entrada `purchase`.
- La recepción y sus movimientos se confirman en una sola transacción.
- El costo actualiza el promedio ponderado por producto y almacén.
- Una compra con recepción no se anula; se compensa mediante devolución a proveedor.

## Activos

### assets

Catalogo de activos. Campos: `company_id`, `internal_code`, `name`, `description`, `management_type`, `status`.

Relaciones: pertenece a empresa; se asigna a sucursales.

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

## Biometria

### biometric_devices

Dispositivos biometricos por empresa/sucursal. Campos: `company_id`, `branch_id`, `name`, `brand`, `model`, `serial_number`, `ip_address`, `port`, `device_id`, `description`, `status`.

Relaciones: pertenece a empresa y sucursal; tiene huellas de clientes.

### customer_biometric_fingerprints

Asociacion cliente-huella-dispositivo. Campos: `company_id`, `customer_id`, `biometric_device_id`, `device_user_id`, `finger_index`, `fingerprint_template`, `description`, `status`.

Relaciones: pertenece a empresa, cliente y dispositivo.
