# 02 - Dashboard

## Qué hace

Muestra indicadores operativos agregados para la empresa y una fecha explícita. El Dashboard no conserva copias de los resultados: calcula cada KPI desde sus tablas fuente para evitar inconsistencias.

## Archivos

- Ruta: `routes/System/Essentials/Dashboard.php`
- Controlador: `DashboardController`
- Servicios: `DashboardService`, `DashboardConfigService`
- Vista/Vue: `resources/views/System/general/Essentials/dashboard`, `resources/js/System/Pages/Essentials/dashboard`
- Tablas: `company_settings`, `sales_header`, `attendances`, `subscriptions`

## Estado por capa

- **Migraciones:** contrato y configuraciones listos.
- **Modelos/servicios:** pendiente implementar consultas agregadas.
- **Pruebas:** pendientes para la fase de servicios; no se crean durante la fase de migraciones.

## KPIs oficiales

### Ventas netas

- Fuente: `sales_header`.
- Filtros obligatorios: `company_id`, `status = active` e `issue_date = fecha consultada`.
- Resultado monetario: `SUM(total)`.
- Resultado documental complementario: `COUNT(id)`.
- No se resta una venta cancelada: se excluye por estado para evitar contarla dos veces.

### Ventas canceladas

- Fuente: `sales_header`.
- Filtros obligatorios: `company_id`, `status = canceled` y `canceled_at` dentro del día consultado según la zona horaria empresarial.
- Resultado monetario: `SUM(total)` como importe cancelado, sin signo negativo.
- Resultado documental: `COUNT(id)`.
- Se usa `canceled_at`, no `issue_date`, porque el indicador representa cancelaciones ocurridas durante el día aunque la venta se haya emitido antes.

### Asistencias del día

- Fuente: `attendances`.
- Filtros obligatorios: `company_id`, `start_date` dentro del día consultado y `status IN (active, finalized)`.
- Resultado: `COUNT(id)`.
- Se excluyen registros `canceled`, `inactive` y entradas sin `start_date`.
- Cada registro representa un ingreso; finalizar el checkout no crea una asistencia adicional.

### Membresías por vencer

- Fuente: `subscriptions`.
- Filtros obligatorios: `company_id`, `status = active` y `end_date` entre el inicio del día consultado y el final de la ventana configurada.
- Resultado: `COUNT(id)`.
- La ventana usa `company_settings.dashboard.membership_expiration_window_days`; el valor inicial es `7` días calendario e incluye el día consultado.
- Una membresía ya vencida antes del inicio del día no forma parte del KPI.

## Configuración temporal

- `company_settings.localization.timezone` define la zona horaria IANA; el valor inicial es `America/Lima`.
- La aplicación debe construir los límites del día en esa zona y convertirlos al criterio temporal usado por la conexión antes de consultar columnas `datetime` o `timestamp`.
- `sales_header.issue_date` ya es `date` y se compara directamente con la fecha empresarial.

## Reglas

- Toda consulta debe filtrar por `company_id`.
- Los totales separan ventas activas y cancelaciones; nunca mezclan ambos estados.
- La fecha y la zona horaria deben ser explícitas.
- Los importes se agregan en base de datos con `SUM`; los conteos usan `COUNT`.
- No se deben usar `get()`, relaciones completas ni colecciones PHP para calcular estos KPIs.
- Una consulta puede devolver varios agregados con expresiones condicionales siempre que conserve las definiciones oficiales.

## Campos relevantes

- `sales_header.company_id`
- `sales_header.issue_date`
- `sales_header.total`
- `sales_header.status`
- `sales_header.canceled_at`
- `attendances.start_date`
- `attendances.status`
- `subscriptions.end_date`
- `subscriptions.status`
- `company_settings.group`, `key`, `value` y `value_type`

## Pendientes para modelos y servicios

- Implementar consultas escalares/agregadas sin cargar modelos completos.
- Agregar pruebas de ventas activas y canceladas, incluyendo una venta emitida en una fecha y cancelada en otra.
- Agregar pruebas de límites horarios, asistencia finalizada y ventana de membresías.
- Incorporar alcance por sucursal cuando el Dashboard permita ese filtro; por ahora los KPIs oficiales son empresariales.
