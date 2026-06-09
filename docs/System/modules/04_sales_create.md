# 04 - Ventas / Nuevo

## Que hace

Permite crear una venta con productos, servicios o membresias. Una venta puede generar efectos secundarios: descuento de stock y creacion de membresias reales.

## Archivos

- Ruta: `routes/System/Sales/Sale.php`
- Controlador: `SaleController`
- Request: `StoreSaleRequest`
- Servicio: `SaleService`
- Vista: `resources/views/System/general/Sales/sales/main.blade.php`
- Vue: `resources/js/System/Pages/Sales/sales/main.vue`
- Tablas: `sales_header`, `sales_body`, `items`, `warehouse_items`, `warehouses`, `subscriptions`

## Reglas

- Debe existir almacen para la sucursal.
- El correlativo se genera por serie.
- El total se calcula desde detalles.
- Si el detalle es `product`, se descuenta stock.
- Si el detalle es `subscription`, se crea membresia real para el cliente.
- Todo se ejecuta dentro de transaccion.
- `SaleConfigService` obtiene sucursales, clientes e ítems mediante `CompanyReferenceDataService`.
- La creación de una venta no elimina la caché de configuración: los registros operativos se consultan por endpoints separados.

## Campos necesarios

- `branch_id`
- `serie_id`
- `holder_id`
- `currency_id`
- `issue_date`
- `details[]`
- Por detalle: `item_id`, `currency_id`, `name`, `quantity`, `price`, `type`, `extras`

## Mejoras sugeridas

- Evitar stock negativo si la empresa no lo permite.
- Revertir stock al anular venta con productos.
- Validar que `serie_id` pertenezca a la sucursal seleccionada.
- Proteger correlativo contra concurrencia.
- Tipar `extras` de membresia con estructura clara.
