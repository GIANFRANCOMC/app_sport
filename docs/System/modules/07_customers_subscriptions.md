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

## Mejoras sugeridas

- Definir si se permiten membresias solapadas.
- Definir regla para `force`.
- Agregar renovacion desde membresia vencida.
- Agregar test de membresia vigente por fecha/sucursal.
