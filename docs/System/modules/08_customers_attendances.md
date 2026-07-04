# 08 - Asistencias de clientes

## Qué hace

Registra el ingreso y la salida de clientes. Valida cliente, membresía vigente, asistencia activa y límite diario permitido por la membresía.

Esta lógica es independiente de `user_attendances`, que controla jornadas laborales de colaboradores.

## Archivos

- Ruta: `routes/System/Customers/TrackingAttendance.php`.
- Controlador: `TrackingAttendanceController`.
- Servicios: `TrackingAttendanceService`, `TrackingAttendanceBusinessService`.
- Requests: `StoreTrackingAttendanceRequest`, `CheckoutTrackingAttendanceRequest`, `ProcessTrackingAttendanceBatchRequest`, `CancelTrackingAttendanceRequest`, `StoreAttendanceCorrectionRequest` y `ReviewAttendanceCorrectionRequest`.
- Vue: `resources/js/System/Pages/Customers/tracking_attendances`.
- Tablas: `attendances`, `customers`, `subscriptions`, `branches`.

## Campos necesarios

- `company_id`.
- `branch_id`.
- `customer_id` o `customer_document_number`.
- `start_date`.
- `end_date`.
- `type`.
- `action`.
- `status`.

## Reglas

- No se permite check-in sin membresía vigente en la sucursal.
- La asistencia activa de un cliente es única por empresa, sucursal y cliente.
- El mismo cliente puede tener historial en varias sucursales, pero no dos asistencias activas en la misma sucursal.
- No se permite un segundo check-in mientras exista una asistencia activa en esa sucursal.
- Checkout requiere una asistencia activa en la sucursal solicitada.
- El ID de la ruta identifica la asistencia exacta que se cerrará; sucursal y cliente se recuperan del registro y no se confía en valores alternativos enviados por el cliente HTTP.
- La asistencia activa se bloquea dentro de la transacción antes de registrar la salida para impedir cierres concurrentes.
- Checkout debe ser posterior al ingreso y al menos dos minutos después.
- El límite diario se obtiene de `subscriptions.attendance_limit_per_day`.
- `exceedsLimit` bloquea un nuevo check-in cuando las asistencias finalizadas del día alcanzan dicho límite.
- El conteo diario considera estados `active` y `finalized`; ignora registros cancelados o inactivos.
- Tipos: `manual_form`, `qr_camera`, `qr_scanner`, `qr_public`, `biometric`.
- `TrackingAttendanceConfigService` carga únicamente sucursales y clientes activos.
- Altas manuales, lotes QR, cancelaciones y correcciones validan empresa y alcance efectivo de sucursal antes de mutar información.
- Cancelar una asistencia no invalida `initParams`, porque no modifica esas opciones.

## Biometría

`TrackingAttendanceBusinessService` usa el namespace correcto `App\Services\System\Devices\BiometricDevices\BiometricDeviceService`.

Para entradas biométricas, el dispositivo y `device_user_id` deben resolver una huella activa del cliente dentro de la misma empresa.

## Pruebas

`tests/Feature/AttendanceFlowsTest.php` verifica:

- Check-in y check-out de cliente.
- Cambio de estado `active` a `finalized`.
- Bloqueo al superar `attendance_limit_per_day`.

## Estado de mejoras

- `daily_limit_scope` define si el límite diario se cuenta por sucursal o por toda la empresa.
- `biometric_device_id` y `source_reference` identifican la lectura exacta.
- `biometric_duplicate_tolerance_seconds` evita duplicados en la ventana configurada.
- `attendance_corrections` conserva valor anterior, valor solicitado, motivo, solicitante, revisor y decisión.
- `GET /tracking_attendances/export` reutiliza filtros y alcance de sucursal del listado, descarga CSV y exige reducir el rango si supera 10 000 registros.
