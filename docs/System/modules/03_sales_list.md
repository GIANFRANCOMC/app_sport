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
