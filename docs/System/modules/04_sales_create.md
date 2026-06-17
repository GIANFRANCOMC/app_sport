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
- La anulación aplica `company_settings.inventory.restore_stock_on_sale_cancellation`; por defecto no repone productos automáticamente.
- Validar que `serie_id` pertenezca a la sucursal seleccionada.
- Proteger correlativo contra concurrencia.
- Tipar `extras` de membresia con estructura clara.

## Actualizacion: impuestos y pagos configurables

- La venta ahora aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `sale` o `both`.
- La venta ahora puede recibir multiples metodos de pago desde `payment_methods`, filtrados por alcance `sale` o `both`, indicando el monto pagado por cada metodo.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `sale_taxes`.
- Los pagos aplicados se guardan como foto del documento en `sale_payments`.
- La vista `resources/js/System/Pages/Sales/sales/main.vue` muestra un bloque lateral de liquidacion con impuestos aplicados automaticamente, metodos de pago, subtotal, impuestos, total, pagado y diferencia.
- Si solo hay un metodo de pago, el importe se sincroniza con el total para facilitar el registro.
- Pendiente: crear pantalla administrativa para impuestos y metodos de pago por empresa.
