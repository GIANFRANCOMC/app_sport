# 12 - Servicios

## Que hace

Administra items de tipo `service`. Los servicios pueden venderse, pero no afectan stock ni crean membresia.

## Archivos

- Ruta: `routes/System/Catalogs/Service.php`
- Controlador: `ServiceController`
- Servicios: `ServiceService`, `ServiceConfigService`
- Requests: `StoreServiceRequest`, `UpdateServiceRequest`
- Tablas: `items`, `category_items`, `categories`

## Campos necesarios

Los mismos de `items`, con `type = service`.

## Reglas

- Categorías y monedas de `initParams` se obtienen mediante los servicios de referencia compartidos.
- No debe descontar stock.
- No debe crear membresía real.
- Puede mostrarse en portal público según `see_my_web`.
- Al crear o editar un servicio se invalida el recurso compartido `items`, actualizando también los artículos disponibles en Ventas.
- Al crear o editar una categoría se limpia la caché de `ServiceConfigService`, por lo que el selector se actualiza en la siguiente carga.

## Mejoras sugeridas

- Permitir duración estimada del servicio si el negocio agenda citas.
- Definir si servicios pueden tener comisiones por vendedor.
