# 03 - Ventas / Listado

## Que hace

Lista ventas realizadas, permite filtrar y acceder a detalle, anulacion o impresion.

## Archivos

- Ruta: `routes/System/Sales/Sale.php`
- Controlador: `SaleController`
- Servicios: `SaleService`, `SaleConfigService`
- Modelos: `SaleHeader`, `SaleBody`
- Vista: `resources/views/System/general/Sales/sales/list.blade.php`
- Vue: `resources/js/System/Pages/Sales/sales/list.vue`
- Tablas: `sales_header`, `sales_body`, `series`, `branches`, `customers`, `currencies`

## Reglas

- Listar solo ventas de series pertenecientes a sucursales de la empresa.
- Filtrar por serie, correlativo, fecha, cliente y estado.
- Permitir anulacion solo si la venta esta `active`.
- Anular una venta no implica necesariamente recibir productos de vuelta.
- `company_settings.inventory.restore_stock_on_sale_cancellation` controla la reposición automática y es `false` por defecto.
- Con la política desactivada, la respuesta recuerda registrar una devolución desde Inventario si la mercancía fue recibida.
- Con la política activa, cada producto genera una entrada `sale_cancellation` en el almacén asociado y la respuesta confirma la reposición.
- Las membresías vinculadas se anulan independientemente de la política de inventario.
- `SaleConfigService` mantiene cachés separadas para `main` y `list`.
- Crear o anular ventas no invalida `initParams`, porque esta configuración contiene filtros, maestros y estados, no registros de venta.

## Campos relevantes

- `sales_header.serie_id`
- `sales_header.sequential`
- `sales_header.holder_id`
- `sales_header.issue_date`
- `sales_header.total`
- `sales_header.status`

## Mejoras sugeridas

- Validar autorizacion por sucursal antes de anular.
- Agregar filtros por rango de fechas.
- Agregar columna de sucursal en listado si no esta visible.
- Agregar test de listado multiempresa.
