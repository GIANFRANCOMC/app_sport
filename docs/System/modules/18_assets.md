# 18 - Activos

## Qué hace

Mantiene el catálogo de bienes y activos controlados por la empresa. Permite clasificarlos, identificarlos físicamente y prepararlos para su asignación a sucursales o colaboradores.

## Archivos

- Ruta: `routes/System/Assets/Asset.php`.
- Controlador: `AssetController`.
- Servicios: `AssetService`, `AssetConfigService`.
- Tablas: `asset_categories`, `assets`.

## Categorías de activos

`asset_categories` clasifica los activos dentro de cada empresa, por ejemplo Equipos de cómputo, Mobiliario, Maquinaria o Herramientas.

Campos:

- `company_id`: empresa propietaria del catálogo.
- `name`: nombre legible de la categoría.
- `description`: alcance o criterio de clasificación.
- `status`: `active` o `inactive`.

Una categoría inactiva conserva el historial, pero no debe ofrecerse al registrar un activo nuevo. El nombre debe validarse en backend dentro de la empresa para evitar categorías duplicadas por diferencias de mayúsculas o espacios.

## Campos del activo

- `company_id`: empresa propietaria del activo.
- `asset_category_id`: categoría opcional; si se elimina la categoría, el activo se conserva sin clasificación.
- `internal_code`: identificador lógico generado por la empresa o el sistema.
- `patrimonial_code`: etiqueta patrimonial asignada por la empresa.
- `serial_number`: serie física proporcionada por el fabricante.
- `name`: nombre del bien.
- `description`: detalle funcional o físico.
- `management_type`: `unit` o `stock`.
- `status`: `active` o `inactive`.

## Identificadores

Los tres identificadores cumplen funciones distintas y no deben reutilizarse entre sí:

- `internal_code` identifica el registro dentro de la plataforma y usa el prefijo configurable de la empresa.
- `patrimonial_code` identifica el bien dentro del control patrimonial de la organización.
- `serial_number` identifica físicamente la unidad según el fabricante.

`patrimonial_code` y `serial_number` son opcionales porque no todos los bienes los poseen. Para `management_type = unit` pueden registrarse ambos. Para `management_type = stock` deben permanecer vacíos mientras el activo se controle como una cantidad agrupada.

## Reglas

- La categoría seleccionada debe pertenecer a la misma empresa y estar activa al crear o reclasificar un activo.
- `internal_code` es obligatorio y único dentro de la empresa según la validación de backend existente.
- Si se informa `patrimonial_code`, debe validarse como único dentro de la empresa ignorando mayúsculas y espacios externos.
- Si se informa `serial_number`, debe validarse como único dentro de la empresa ignorando mayúsculas y espacios externos.
- Los activos pueden asignarse a sucursales y usuarios desde Gestión de activos.
- Inactivar una categoría no inactiva automáticamente sus activos.
- Eliminar una categoría no elimina activos; `asset_category_id` queda nulo.

## Configuración compartida

- El código interno usa `company_settings.internal_code_prefixes.asset`; `ACT` es el valor inicial.
- El formulario muestra el prefijo como parte visual del mismo control y conserva separada la porción editable.
- `InternalCodeService` compone el código definitivo en backend. Un valor nulo o vacío permite códigos sin prefijo.
- Store y Update extienden `CompanyFormRequest`; los errores inline no repiten el label y el resumen sí identifica el campo.

## Implementado en migraciones

- Catálogo `asset_categories` segmentado por `company_id`.
- Relación opcional `assets.asset_category_id` con `nullOnDelete()`.
- Campos `assets.patrimonial_code` y `assets.serial_number` separados y opcionales.
- Límites de 100 caracteres para código patrimonial y 150 para serie física.

## Pendiente para la fase de modelos y frontend

- Crear modelo, servicio, validaciones y alta rápida para categorías de activos.
- Incorporar categoría, código patrimonial y serie física en crear, editar, listar, buscar y exportar activos.
- Aplicar las reglas diferenciadas entre activos unitarios y activos administrados por stock.
- Definir si los bienes administrados por stock necesitarán una futura tabla de unidades físicas cuando deban individualizarse por serie.
