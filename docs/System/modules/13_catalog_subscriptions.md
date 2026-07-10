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
