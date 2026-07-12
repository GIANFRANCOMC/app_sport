# 13 - Membresías de catálogo

## Qué hace

Administra ítems `subscription` que, al venderse, generan una membresía real para cliente y sucursal.

## Backend

- Duración y tipo de duración son obligatorios.
- `attendance_limit_per_day` permite definir un límite diario específico del plan.
- `benefits` y `restrictions` son arreglos JSON validados, con hasta 50 textos de 255 caracteres.
- `price_includes_tax` determina si el IGV ya está incluido.
- `commission_type` y `commission_value` permiten configurar comision por membresia vendida; la venta guarda la foto en `sales_body` y suma el total interno en `sales_header.commission_total`.
- Código, moneda, categorías y pertenencia se validan por empresa.

## Interfaz

- El listado muestra límite diario y comisión junto al nombre para distinguir planes comerciales sin abrir el formulario.
- El formulario permite definir límite diario de asistencias, comisión y valor de comisión.
- `benefits` y `restrictions` se editan como textos separados por coma para mantener una captura rápida; el frontend los normaliza a arreglos antes de enviarlos al backend.
- Los beneficios describen lo que incluye el plan; las restricciones deben usarse para reglas comerciales claras como horarios, sedes o condiciones de uso.

## Actualizacion funcional: cupos y vencimiento

- Las membresias de catalogo no usan `barcode`, `brand_id` ni `warehouse_items`; su disponibilidad no depende de inventario fisico.
- `capacity_control_enabled`, `capacity_limit` y `capacity_used` permiten vender membresias con cupos limitados o tratarlas como ilimitadas.
- `expires_at` es opcional. Cuando vence, el backend inactiva la membresia al listar catalogo o referencias comerciales y la bloquea en ventas.
- El listado muestra cupos disponibles para diferenciar planes ilimitados, promociones cerradas o paquetes con disponibilidad limitada.
- Al vender una membresia limitada se consume cupo segun la cantidad vendida. Al anular la venta se repone el cupo consumido.
