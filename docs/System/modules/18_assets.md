# 18 - Activos

## Que Hace

Administra bienes fisicos por empresa, con clasificacion, identificacion interna, control patrimonial y serie del fabricante.

## Backend

- `asset_categories`: nombre, descripcion y estado por empresa.
- `assets.asset_category_id`: categoria opcional.
- `assets.internal_code`: identificador del sistema.
- `assets.patrimonial_code`: codigo patrimonial opcional y unico por empresa.
- `assets.serial_number`: serie fisica opcional y unica por empresa.
- Modelo `AssetCategory`, relaciones en `Asset` y validaciones de pertenencia/unicidad.
- Endpoints protegidos para listar, crear y editar categorias bajo `/assets/categories`.
- `StoreAssetCategoryRequest` y `UpdateAssetCategoryRequest` reutilizan `CompanyFormRequest`, normalizan textos y aplican unicidad empresarial antes de persistir.
- Las operaciones de asignacion y retiro usan requests especificos: `AssignAssetToBranchRequest`, `UnassignAssetFromBranchRequest`, `UpdateAssetInBranchRequest`, `AssignAssetToUserRequest` y `UnassignAssetFromUserRequest`.
- Los requests de gestion de activos validan arrays completos antes de llegar al servicio; el servicio conserva la validacion de pertenencia a sucursal y activo como segunda barrera.
- Busqueda de activos por codigo interno, codigo patrimonial, serie, nombre o descripcion.

## Seguridad

- Ninguna asignacion acepta `company_id` desde frontend.
- La sucursal se resuelve por `company_id + branch_id`.
- Las asignaciones de colaboradores validan que la cantidad total no exceda la cantidad disponible en la sucursal.
- Cada asignacion, retiro o devolucion registra un evento en `asset_assignment_logs`.

## Criterio Pendiente

La individualizacion de unidades de un bien administrado como stock sigue siendo una decision de negocio: solo debe crearse una tabla de unidades fisicas cuando cada unidad necesite serie, mantenimiento o ciclo de vida propio.
