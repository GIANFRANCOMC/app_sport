# 12 - Servicios

## Qué hace

Administra ítems `service`: se venden, no descuentan stock y pueden medirse dentro de una sesión de servicio.

## Backend

- Usa `items` con `type = service` y categorías mediante `category_items`.
- `estimated_duration_minutes` conserva la duración prevista para agenda, SLA y comparación con tiempo real.
- `commission_rate` admite una comisión porcentual opcional entre 0 y 100.
- `price_includes_tax` define si el precio ya contiene IGV.
- Moneda, categoría y código interno se validan por empresa.
- Crear o editar invalida las configuraciones dependientes de ventas y catálogo.
