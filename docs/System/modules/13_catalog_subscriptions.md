# 13 - Membresias de catalogo

## Que hace

Administra items de tipo `subscription`, que representan planes o membresias vendibles.

## Archivos

- Ruta: `routes/System/Catalogs/Subscription.php`
- Controlador: `SubscriptionController`
- Servicios: `SubscriptionService`, `SubscriptionConfigService`
- Requests: `StoreSubscriptionRequest`, `UpdateSubscriptionRequest`
- Tablas: `items`, `subscriptions` cuando se vende

## Campos necesarios

- Campos base de item.
- `type = subscription`
- `duration_type`
- `duration_value`
- `see_my_web`
- `see_my_web_price`

## Reglas

- Al venderse, genera registro en `subscriptions`.
- Debe definir duracion para calcular vigencia.
- La membresia real queda asociada a cliente y sucursal.

## Mejoras sugeridas

- Agregar limite diario configurable en item de catalogo.
- Agregar beneficios o restricciones por plan.
- Validar que duracion no sea nula si se vendera.

