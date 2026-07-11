# 26 - Perfiles y accesos

## Qué hace

Define qué módulos, acciones, sucursales, cajas y almacenes puede utilizar cada colaborador.

## Backend

- `role_sub_sections.actions` concede `view`, `create`, `update`, `delete`, `export`, `import` y `operate` por módulo.
- Los alcances `all`/`restricted` se guardan en el perfil; el colaborador puede heredarlos o reducirlos.
- `module.permission` valida módulo/acción y `resource.scope` valida recursos operativos.
- Un usuario no puede delegar más permisos o alcance del que posee.
- No puede retirarse el último administrador activo de una empresa.
- Antes de guardar cambios que inactivan un perfil, retiran acceso total o reducen permisos, la UI advierte cuántos usuarios activos quedarán afectados.
- El backend mantiene la regla final: si el cambio deja a la empresa sin administrador activo, se bloquea aunque la UI haya mostrado confirmación.
- `POST /roles/{id}/duplicate` copia permisos y alcances a un perfil nuevo sin acceso total.
- `DuplicateRoleRequest` normaliza el nombre y valida unicidad dentro de la empresa antes de copiar el perfil.
- Cambios de perfil, permiso y recursos invalidan únicamente las cachés afectadas.
- `business_audit_logs` registra creación, cambios de permisos y alcances con el conteo de usuarios afectados, sin guardar secretos.

## Frontend

- El listado permite crear, editar y duplicar perfiles de acceso.
- La acción `Duplicar perfil` solicita un nombre nuevo, copia permisos y alcances mediante `POST /roles/{id}/duplicate` y refresca el listado al terminar.
- El nombre sugerido usa `Copia de {perfil}` y evita repetir nombres ya visibles en la página actual; la validación final queda en el backend por empresa.
- Las acciones de tabla se mantienen compactas con `br-table-actions` y botones `br-icon-action-*` para conservar la línea visual de Productos.
