# 92 - Series y almacenes

## Que hace

Entidades tecnicas que se crean o usan alrededor de sucursales.

## Series

Tabla `series`. Sirve para correlativos de ventas por sucursal y tipo de documento.

Campos importantes: `branch_id`, `document_type_id`, `code`, `number`, `init`, `status`.

## Almacenes

Tablas `warehouses` y `warehouse_items`. Sirven para manejar stock por sucursal.

Campos importantes:

- `warehouses.branch_id`
- `warehouses.name`
- `warehouse_items.warehouse_id`
- `warehouse_items.item_id`
- `warehouse_items.quantity`

## Reglas

- Una venta necesita serie.
- Una venta de producto necesita almacen de sucursal.
- Al crear sucursal puede crearse almacen y series por defecto.

## Mejoras sugeridas

- Bloquear ventas si no existe serie activa.
- Bloquear venta si no existe almacen, pero mostrar solucion clara en UI.
- Agregar historial de correlativos y movimientos.

