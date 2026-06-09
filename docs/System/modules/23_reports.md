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

## Mejoras sugeridas

- Documentar parametros de cada reporte.
- Agregar pruebas de PDF.
- Controlar memoria/tiempo en exportes grandes.
- Estandarizar nombres de archivo.
