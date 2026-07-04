# 18 - Activos

## Qué hace

Administra bienes físicos por empresa, con clasificación, identificación interna, control patrimonial y serie del fabricante.

## Backend

- `asset_categories`: nombre, descripción y estado por empresa.
- `assets.asset_category_id`: categoría opcional.
- `assets.internal_code`: identificador del sistema.
- `assets.patrimonial_code`: código patrimonial opcional y único por empresa.
- `assets.serial_number`: serie física opcional y única por empresa.
- Modelo `AssetCategory`, relaciones en `Asset` y validaciones de pertenencia/unicidad.
- Endpoints protegidos para listar, crear y editar categorías bajo `/assets/categories`.
- Búsqueda de activos por código interno, código patrimonial, serie, nombre o descripción.


La individualización de unidades de un bien administrado como stock sigue siendo una decisión de negocio: solo debe crearse una tabla de unidades físicas cuando cada unidad necesite serie, mantenimiento o ciclo de vida propio.
