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

## Estado de mejoras

- `notifications:send-subscriptions` procesa lotes y se ejecuta cada cinco minutos sin solaparse.
- El comando itera únicamente tenants activos, activa la conexión antes de consultar, desconecta en `finally` y audita el resultado en landlord.
- Admite `--tenant`, `--company` y `--limit` para operación controlada sin compartir contexto entre clientes.
- Cada notificación conserva intentos, máximo, próxima ejecución, envío, fallo y último error.
- Los reintentos usan espera incremental y terminan al alcanzar `max_attempts`.
- El disparador HTTP heredado permanece autenticado, limitado y protegido por permisos; no existe una ruta pública de envío.
