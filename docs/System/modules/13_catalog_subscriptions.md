# 13 - Membresías de catálogo

## Qué hace

Administra items de tipo `subscription`, que representan planes o membresías vendibles.

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

- Categorías y monedas de `initParams` se obtienen mediante los servicios de referencia compartidos.
- Al venderse, genera registro en `subscriptions`.
- Debe definir duración para calcular vigencia.
- La membresía real queda asociada a cliente y sucursal.
- Al crear o editar una membresía se invalida el recurso compartido `items`, actualizando también los artículos disponibles en Ventas.
- Al crear o editar una categoría se limpia la caché de `SubscriptionConfigService`, por lo que el selector se actualiza en la siguiente carga.

## Mejoras sugeridas

- Agregar límite diario configurable en item de catálogo.
- Agregar beneficios o restricciones por plan.
- Validar que duración no sea nula si se venderá.
