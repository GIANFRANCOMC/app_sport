# 15 - Gestion de stock

## Que hace

Administra cantidades de productos por almacen/sucursal.

## Archivos

- Ruta: `routes/System/Warehouses/StockManagement.php`
- Controlador: `StockManagementController`
- Servicios: `StockManagementService`, `StockManagementConfigService`
- Tablas: `warehouses`, `warehouse_items`, `items`, `branches`

## Campos necesarios

- `warehouse_id`
- `item_id`
- `quantity`
- `status`

## Reglas

- El almacen debe pertenecer a una sucursal de la empresa.
- Solo productos deberian manejar stock.
- Ventas de productos descuentan stock.

## Mejoras sugeridas

- Crear tabla de movimientos de stock.
- Definir stock minimo, alertas y ajustes con motivo.
- Bloquear stock negativo si se decide.

