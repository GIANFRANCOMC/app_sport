# 09 - Notificaciones de clientes

## Que hace

Permite revisar emails relacionados a suscripciones y su estado de envio.

## Archivos

- Ruta: `routes/System/Customers/TrackingNotification.php`
- Controlador: `TrackingNotificationController`
- Servicios: `TrackingNotificationService`, `NotificationService`
- Modelo: `SubscriptionEmail`
- Vista/Vue: `resources/js/System/Pages/Customers/tracking_notifications`
- Tablas: `subscription_emails`

## Campos necesarios

- `to`
- `subject`
- `body`
- `extras_json`
- `type`
- `model_id`
- `model_type`
- `status`

## Reglas

- Estados: `pending`, `sent`, `failed`.
- El envio deberia poder reintentarse.
- El contenido debe estar asociado a una membresia o modelo relacionado.

## Mejoras sugeridas

- Mover envio masivo a comando programado protegido.
- Guardar error del ultimo intento.
- Agregar reintentos controlados.
- Evitar ruta publica directa para enviar emails.

