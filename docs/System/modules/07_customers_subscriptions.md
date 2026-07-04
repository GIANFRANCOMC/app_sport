# 07 - Membresias de clientes

## Que hace

Lista y administra membresias reales asignadas a clientes. Estas pueden originarse por venta o por registro manual.

## Archivos

- Ruta: `routes/System/Customers/TrackingSubscription.php`
- Controlador: `TrackingSubscriptionController`
- Servicio: `TrackingSubscriptionService`
- Request: `CancelTrackingSubscriptionRequest`
- Vue: `resources/js/System/Pages/Customers/tracking_subscriptions`
- Tablas: `subscriptions`, `customers`, `branches`, `sales_header`, `sales_body`

## Campos necesarios

- `company_id`
- `branch_id`
- `customer_id`
- `start_date`
- `end_date`
- `duration_type`
- `duration_value`
- `attendance_limit_per_day`
- `type`
- `status`

## Reglas

- Una membresia vigente permite registrar asistencia.
- La membresia debe pertenecer a empresa y sucursal.
- Cancelar requiere motivo.
- Si viene de venta, la anulacion de venta tambien la cancela.
- `TrackingSubscriptionConfigService` carga únicamente sucursales y clientes activos.
- Cancelar una membresía no invalida `initParams`, porque no modifica esas opciones.

## Estado de mejoras

- `company_settings.subscriptions.overlap_policy` define `block` o `allow` por empresa.
- `force=true` es una excepción explícita enviada por una operación autorizada; nunca es el valor por defecto.
- `POST /tracking_subscriptions/{id}/renew` crea una nueva membresía manual y conserva `renewed_from_id`.
- La renovación respeta empresa, sucursal, alcance, fechas y política de solapamiento.
- Las pruebas se añadirán cuando sean solicitadas expresamente.
