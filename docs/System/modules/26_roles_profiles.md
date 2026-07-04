# 26 - Perfiles y accesos

## Qué hace

Define qué módulos, acciones, sucursales, cajas y almacenes puede utilizar cada colaborador.

## Backend

- `role_sub_sections.actions` concede `view`, `create`, `update`, `delete`, `export`, `import` y `operate` por módulo.
- Los alcances `all`/`restricted` se guardan en el perfil; el colaborador puede heredarlos o reducirlos.
- `module.permission` valida módulo/acción y `resource.scope` valida recursos operativos.
- Un usuario no puede delegar más permisos o alcance del que posee.
- No puede retirarse el último administrador activo de una empresa.
- `POST /roles/{id}/duplicate` copia permisos y alcances a un perfil nuevo sin acceso total.
- `DuplicateRoleRequest` normaliza el nombre y valida unicidad dentro de la empresa antes de copiar el perfil.
- Cambios de perfil, permiso y recursos invalidan únicamente las cachés afectadas.
- `business_audit_logs` registra los cambios sensibles sin guardar secretos.
