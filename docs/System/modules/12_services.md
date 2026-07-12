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

## Actualizacion funcional: cupos y vencimiento

- Servicios no usa `barcode`, `brand_id` ni `warehouse_items`: no tiene codigo de barras, marca comercial ni inventario fisico por unidades.
- `capacity_control_enabled`, `capacity_limit` y `capacity_used` controlan cupos opcionales. Si el control esta apagado, el servicio es ilimitado.
- `expires_at` es opcional. Al vencer, el backend inactiva el servicio al listar catalogo o referencias comerciales y bloquea ventas con datos obsoletos.
- El listado muestra cupos disponibles como dato compacto: ilimitado o disponibles contra limite.
- El formulario permite activar cupos y definir fecha de vencimiento. Si cupos esta apagado, el limite queda deshabilitado para evitar datos que no aplican.
- Ventas y Venta POS solo reciben servicios `active`, no vencidos y con cupos disponibles cuando el control esta activo.
- Cada venta consume cupos segun la cantidad vendida. La anulacion de una venta repone esos cupos para servicios y membresias.
