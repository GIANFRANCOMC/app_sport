# 12 - Servicios

## Qué hace

Administra ítems `service`: se venden, no descuentan stock y pueden medirse dentro de una sesión de servicio.

## Backend

- Usa `items` con `type = service` y categorías mediante `category_items`.
- `estimated_duration_minutes` conserva la duración prevista para agenda, SLA y comparación con tiempo real.
- `commission_type` y `commission_value` permiten configurar comision por servicio como porcentaje o monto fijo por unidad vendida.
- `commission_rate` se conserva como compatibilidad historica para servicios que ya tenian comision porcentual.
- En ventas, la comision se guarda como foto en `sales_body` y se suma en `sales_header.commission_total`; no modifica el total cobrado al cliente.
- `price_includes_tax` define si el precio ya contiene IGV.
- Moneda, categoría y código interno se validan por empresa.
- Crear o editar invalida las configuraciones dependientes de ventas y catálogo.

## Interfaz

- El listado muestra duración estimada y comisión para que el usuario compare servicios sin abrir cada modal.
- El formulario permite definir duración estimada en minutos, tipo de comisión (`Sin comisión`, `Porcentaje` o `Monto fijo por unidad`) y valor.
- Si la comisión está en `Sin comisión`, el valor queda deshabilitado para evitar montos huérfanos.
- La duración sirve como base para agenda, servicios en curso, SLA y medición del tiempo real del responsable.
