# 23 - Reportes

## Que hace

Genera reportes y exportes de clientes, items, sucursales, ventas, usuarios y comprobantes PDF.

## Archivos

- Ruta: `routes/System/Essentials/Report.php`
- Controlador: `ReportController`
- Servicio: `ReportConfigService`
- Exports: `app/Exports`
- PDF: `resources/views/System/pdf/sales`
- Tablas: `sales_header`, `sales_body`, `customers`, `items`, `branches`, `users`

## Reglas

- Todo reporte debe respetar empresa.
- PDF de venta puede generarse en A4 o ticket 80mm.
- Exportes deben filtrar por parametros enviados.

## Estado de mejoras

- Todos los queries se filtran por `company_id` y, cuando corresponde, por sucursales autorizadas.
- `reports.export_max_rows` rechaza exportaciones excesivas con un mensaje accionable.
- Los archivos usan `gympe-{recurso}-{Ymd-His}.{extensión}`.
- Ventas admite `by_month`, `range_months`, `by_date` y `range_dates`; clientes, usuarios, items y sucursales aceptan sus filtros documentados por endpoint.
- El PDF obtiene la empresa actual, verifica alcance de sucursal, valida base64 estricto y admite la fecha de expiración completa.
- Las pruebas PDF se añadirán cuando sean solicitadas expresamente.
