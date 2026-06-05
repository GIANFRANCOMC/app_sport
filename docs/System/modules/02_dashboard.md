# 02 - Dashboard

## Que hace

Muestra indicadores de operacion para la empresa, principalmente datos por fecha como ventas y sucursales.

## Archivos

- Ruta: `routes/System/Essentials/Dashboard.php`
- Controlador: `DashboardController`
- Servicios: `DashboardService`, `DashboardConfigService`
- Vista/Vue: `resources/views/System/general/Essentials/dashboard`, `resources/js/System/Pages/Essentials/dashboard`
- Tablas: `branches`, `sales_header`

## Reglas

- La informacion debe filtrarse por `company_id`.
- Los totales deben excluir o separar ventas canceladas segun el indicador.
- La fecha del dashboard debe ser explicita para evitar confusiones.

## Campos relevantes

- `branches.company_id`
- `sales_header.issue_date`
- `sales_header.total`
- `sales_header.status`

## Mejoras sugeridas

- Definir KPIs oficiales: ventas netas, ventas canceladas, asistencias del dia, membresias por vencer.
- Agregar pruebas de calculo para ventas activas/canceladas.
- Evitar cargar registros completos si solo se necesitan agregados.

