# 15 - Gestion de stock

## Qué hace

Administra cantidades de productos por almacén y sucursal. Muestra el mínimo configurado para el producto en el almacén seleccionado y alerta cuando la cantidad actual lo alcanza.

## Archivos

- Ruta: `routes/System/Warehouses/StockManagement.php`
- Controlador: `StockManagementController`
- Servicios: `StockManagementService`, `StockManagementConfigService`
- Tablas: `warehouses`, `warehouse_items`, `items`, `branches`

## Campos necesarios

- `warehouse_id`
- `item_id`
- `quantity`
- `minimum_stock`
- `status`

## Reglas

- El almacén debe pertenecer a una sucursal de la empresa.
- Solo productos deben manejar stock.
- Ventas de productos descuentan stock.
- El estado de stock mínimo compara `quantity <= minimum_stock`; ya no utiliza un umbral fijo.
- El stock mínimo se configura desde Productos y puede ser diferente por almacén.

## Mejoras sugeridas

- Crear tabla de movimientos de stock.
- Agregar alertas globales y notificaciones para mínimos alcanzados.
- Bloquear stock negativo si se decide.
