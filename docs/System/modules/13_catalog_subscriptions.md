# 13 - Membresías de catálogo

## Qué hace

Administra ítems `subscription` que, al venderse, generan una membresía real para cliente y sucursal.

## Backend

- Duración y tipo de duración son obligatorios.
- `attendance_limit_per_day` permite definir un límite diario específico del plan.
- `benefits` y `restrictions` son arreglos JSON validados, con hasta 50 textos de 255 caracteres.
- `price_includes_tax` determina si el IGV ya está incluido.
- Código, moneda, categorías y pertenencia se validan por empresa.

La presentación de beneficios, restricciones y límites está en `docs/UI_UX_PENDING.md`.
