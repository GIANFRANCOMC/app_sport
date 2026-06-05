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

- No debe descontar stock.
- No debe crear membresia real.
- Puede mostrarse en portal publico segun `see_my_web`.

## Mejoras sugeridas

- Permitir duracion estimada del servicio si el negocio agenda citas.
- Definir si servicios pueden tener comisiones por vendedor.

