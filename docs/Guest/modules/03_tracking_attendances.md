# 03 - Asistencia pública

## Qué hace

Permite registrar asistencia de clientes desde un enlace o QR temporal de una sucursal.

## Archivos

- Ruta: `routes/Guest/TrackingAttendance.php`
- Controlador: `Guest/TrackingAttendanceController`
- Middleware: `EnsurePublicAttendanceAccess`
- Servicio: `TrackingAttendanceBusinessService`
- Generador del enlace: `BranchController::publicAttendanceLink`
- Tablas: `branches`, `customers`, `subscriptions`, `attendances`

## Flujo seguro

1. Un usuario System autorizado solicita `GET /branches/{id}/public-attendance-link`.
2. Backend valida empresa, sucursal activa y vencimiento entre 5 minutos y 7 días.
3. El visitante abre la URL firmada.
4. El GET validado guarda en sesión una capacidad temporal con empresa, sucursal y expiración.
5. El POST de lectura QR exige esa capacidad, coincidencia de empresa/sucursal y rate limit.

La ruta legacy basada únicamente en base64 responde 404 y no concede acceso.

## Reglas

- Usa tipo `qr_public` y no expone datos completos del cliente.
- La sucursal debe pertenecer a la empresa activa.
- La membresía debe estar vigente y respetar el límite diario.
- `customer_attendance.auto_checkout_active` define si una lectura posterior cierra automáticamente una asistencia activa; por defecto permanece desactivado.
- Los límites antiabuso se obtienen desde `config/public_access.php`.
