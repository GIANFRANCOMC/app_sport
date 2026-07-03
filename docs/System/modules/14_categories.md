# 14 - Categorías

## Qué hace

Agrupa productos, servicios y membresías por empresa.

## Backend

- Campos: `company_id`, `internal_code`, `name`, `description`, `sort_order`, `is_public` y `status`.
- `sort_order` controla orden estable; `is_public` separa uso interno de exposición pública.
- Solo categorías activas se ofrecen en nuevas asociaciones.
- Eliminar queda bloqueado cuando existen ítems activos asociados.
- Crear, editar o eliminar invalida las cachés dependientes de Productos, Servicios y Membresías.
- La alta rápida conserva generación de código y validación backend.

La administración visual de orden y publicación está en `docs/UI_UX_PENDING.md`.
