# 17 - Sucursales

## Que hace

Administra sedes fisicas de la empresa.

## Archivos

- Ruta: `routes/System/Organizations/Branch.php`
- Controlador: `BranchController`
- Servicios: `BranchService`, `BranchConfigService`, `SerieService`, `WarehouseService`
- Tabla: `branches`

## Campos necesarios

- `company_id`
- `internal_code`
- `name`
- `address`
- `reference`
- `telephone`
- `email`
- `capacity`
- `map_url`
- `status`

## Reglas

- Cada sucursal pertenece a una empresa.
- Al crear sucursal pueden crearse series y almacen por defecto.
- Ventas, asistencias, activos y biometricos dependen de sucursal.
- Crear o editar una sucursal invalida también `ProductConfigService`, porque Productos carga almacenes por sucursal.

## Estado de mejoras

- Crear una sucursal genera sus series activas por tipo documental y un almacén predeterminado con relaciones de producto en cero.
- `capacity` se valida como entero no negativo y queda disponible para aforo de mesas/estaciones.
- No puede inactivarse una sucursal con activos asignados a colaboradores.
- La vista incorpora una seccion plegable de auditoria de series para consultar emisiones, anulaciones, responsable, origen y saltos de correlativo.
- La auditoria de series reutiliza `GET /branches/series/audit` y descarga CSV mediante `GET /branches/series/audit/export`.

## Configuración y validación compartida

- El código interno usa `company_settings.internal_code_prefixes.branch`; `SUC` es el valor inicial.
- El addon visual y la normalización backend se desactivan cuando el valor configurado es nulo o vacío.
- Store y Update extienden `CompanyFormRequest`, normalizan cadenas y aplican `AppliesInternalCodePrefix`.
- Los resúmenes de validación recuperan el nombre del campo; los mensajes bajo el control permanecen compactos.
