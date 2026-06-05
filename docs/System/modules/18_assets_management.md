# 18 - Gestion de activos

## Que hace

Gestiona asignaciones de activos a sucursales y usuarios.

## Archivos

- Ruta: `routes/System/Assets/AssetManagement.php`
- Controlador: `AssetManagementController`
- Servicio: `AssetManagementService`
- Tablas: `branch_assets`, `asset_assignments`, `asset_assignment_logs`, `assets`, `branches`, `users`

## Campos necesarios

- Para sucursal: `branch_id`, `asset_id`, `quantity`, `acquisition_value`, `acquisition_date`, `note`, `status`.
- Para usuario: `user_id`, `branch_id`, `asset_id`, `quantity`, `status`.

## Reglas

- No asignar a usuarios mas cantidad que la disponible en sucursal.
- Retirar marca como `retired`, no borra fisicamente.
- Estados: `active`, `maintenance`, `retired`.

## Mejoras sugeridas

- Usar `asset_assignment_logs` en cada movimiento.
- Bloquear retiro de sucursal si hay usuarios con asignaciones activas.
- Agregar transferencias entre usuarios/sucursales.

