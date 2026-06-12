# 18 - Activos

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

## Configuración y validación compartida

- El código interno usa `company_settings.internal_code_prefixes.asset`; `ACT` es el valor inicial.
- El formulario muestra el prefijo como parte visual del mismo control y conserva separada la porción editable.
- `InternalCodeService` compone el código definitivo en backend. Un valor nulo o vacío permite códigos sin prefijo.
- Store y Update extienden `CompanyFormRequest`; los errores inline no repiten el label y el resumen sí identifica el campo.
- Definir reglas distintas para activos unitarios vs stock.
