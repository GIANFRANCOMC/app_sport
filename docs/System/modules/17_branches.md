# 17 - Sucursales

## Qué hace

Administra sedes físicas de la empresa.

## Archivos

- Ruta: `routes/System/Organizations/Branch.php`
- Controlador: `BranchController`
- Servicios: `BranchService`, `BranchConfigService`, `SerieService`, `WarehouseService`
- Tablas: `branches`, `series`, `warehouses`, `series_correlative_movements`

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

## Separación de configuración

- Fiscal: series, documento y correlativos asociados a la sede.
- Pública: dirección, referencia, teléfono, correo y mapa visibles para clientes.
- Operativa: capacidad, estado, almacenes, cajas, ventas y reportes dependientes.

La vista muestra estos ámbitos en una guía superior para aclarar qué datos impactan a clientes y cuáles afectan operación interna.

## Reglas

- Cada sucursal pertenece a una empresa.
- Al crear sucursal pueden crearse series y almacén por defecto.
- Ventas, asistencias, activos, cajas, almacenes y biométricos dependen de sucursal.
- Crear o editar una sucursal invalida también `ProductConfigService`, porque Productos carga almacenes por sucursal.

## Estado de mejoras

- Crear una sucursal genera sus series activas por tipo documental y un almacén predeterminado con relaciones de producto en cero.
- `capacity` se valida como entero no negativo y queda disponible para aforo de mesas/estaciones.
- No puede inactivarse una sucursal con activos asignados a colaboradores.
- La vista incorpora una sección plegable de auditoría de series para consultar emisiones, anulaciones, responsable, origen y saltos de correlativo.
- La auditoría de series reutiliza `GET /branches/series/audit` y descarga CSV mediante `GET /branches/series/audit/export`.

## Configuración y validación compartida

- El código interno usa `company_settings.internal_code_prefixes.branch`; `SUC` es el valor inicial.
- El addon visual y la normalización backend se desactivan cuando el valor configurado es nulo o vacío.
- Store y Update extienden `CompanyFormRequest`, normalizan cadenas y aplican `AppliesInternalCodePrefix`.
- Los resúmenes de validación recuperan el nombre del campo; los mensajes bajo el control permanecen compactos.

## Pendientes sugeridos

- Convertir la gestión de series, almacenes y cajas por sucursal en subpaneles dedicados cuando el flujo operativo lo requiera.
