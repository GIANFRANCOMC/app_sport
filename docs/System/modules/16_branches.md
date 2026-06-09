# 16 - Sucursales

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

## Mejoras sugeridas

- Documentar exactamente efectos automaticos al crear sucursal.
- Validar capacidad para futuras reglas de aforo.
- Agregar coordenadas si se requiere mapa.
