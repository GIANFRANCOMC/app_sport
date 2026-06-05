# 08 - Asistencias

## Que hace

Registra check-in y checkout de clientes. Valida cliente, membresia vigente, asistencia activa y limite diario.

## Archivos

- Ruta: `routes/System/Customers/TrackingAttendance.php`
- Controlador: `TrackingAttendanceController`
- Servicios: `TrackingAttendanceService`, `TrackingAttendanceBusinessService`
- Request: `CancelTrackingAttendanceRequest`
- Vue: `resources/js/System/Pages/Customers/tracking_attendances`
- Tablas: `attendances`, `customers`, `subscriptions`, `branches`

## Campos necesarios

- `company_id`
- `branch_id`
- `customer_id` o `customer_document_number`
- `start_date`
- `end_date`
- `type`
- `action`
- `status`

## Reglas

- No se permite check-in sin membresia vigente en la sucursal.
- No se permite segundo check-in si ya hay asistencia activa.
- Checkout requiere asistencia activa.
- Checkout debe ser posterior al ingreso y al menos 2 minutos despues.
- Tipos: `manual_form`, `qr_camera`, `qr_scanner`, `qr_public`, `biometric`.

## Mejoras sugeridas

- Usar `exceedsLimit` para bloquear limite diario si todavia no se aplica.
- Corregir namespace del servicio biometrico si esta inconsistente.
- Agregar test de check-in/check-out.
- Definir si la asistencia activa es unica por empresa o por sucursal.

