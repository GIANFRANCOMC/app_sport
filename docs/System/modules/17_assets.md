# 17 - Activos

## Que hace

Catalogo de bienes/activos que la empresa controla.

## Archivos

- Ruta: `routes/System/Assets/Asset.php`
- Controlador: `AssetController`
- Servicios: `AssetService`, `AssetConfigService`
- Tabla: `assets`

## Campos necesarios

- `company_id`
- `internal_code`
- `name`
- `description`
- `management_type`
- `status`

## Reglas

- `management_type` puede ser `unit` o `stock`.
- Los activos pueden asignarse a sucursales y usuarios desde gestion de activos.

## Mejoras sugeridas

- Agregar categoria de activo.
- Agregar codigo patrimonial o serie fisica.
- Definir reglas distintas para activos unitarios vs stock.

