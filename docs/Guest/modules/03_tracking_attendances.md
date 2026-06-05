# 03 - Asistencia publica

## Que hace

Permite registrar asistencia desde enlace/QR publico de una sucursal.

## Archivos

- Ruta: `routes/Guest/TrackingAttendance.php`
- Controlador: `Guest/TrackingAttendanceController`
- Servicio reutilizado: `TrackingAttendanceBusinessService`
- Vista/Vue: `resources/views/Guest/general/tracking_attendances`, `resources/js/Guest/Pages/tracking_attendances`
- Tablas: `branches`, `customers`, `subscriptions`, `attendances`

## Reglas

- La sucursal se valida contra empresa.
- Usa tipo de asistencia `qr_public`.
- Debe validar membresia vigente.
- No debe exponer datos completos de cliente.

## Mejoras sugeridas

- Reemplazar base64 de sucursal por token firmado.
- Agregar FormRequest.
- Agregar rate limiting.
- Permitir checkout automatico si ya hay asistencia activa, si el negocio lo aprueba.

