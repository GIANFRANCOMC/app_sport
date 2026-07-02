# 26 - Perfiles de acceso

## Que hace

Administra perfiles de acceso para colaboradores de una empresa. Tecnicamente se almacenan en `roles`, pero en la interfaz se nombran como perfiles para que el usuario entienda que esta definiendo el alcance de cada colaborador.

El permiso combina módulo y acción. Si el perfil no tiene una subsección asignada, el colaborador no ve el acceso en el menú ni puede ingresar por URL directa. Si posee el módulo, `role_sub_sections.actions` define qué puede consultar, crear, editar, eliminar, exportar, importar u operar.

## Archivos

- Ruta: `routes/System/Organizations/Role.php`
- Controlador: `RoleController`
- Servicios: `RoleService`, `RoleConfigService`, `RolePermissionService`
- Request: `StoreRoleRequest`
- Middleware: `EnsureModulePermission`
- Vista Blade: `resources/views/System/general/Organizations/roles/main.blade.php`
- Pagina Vue: `resources/js/System/Pages/Organizations/roles/main.vue`
- Entry Vue: `resources/js/System/Pages/Organizations/roles/main.js`, montado con `mountEntityApp(App)`
- Helper Vue compartido: `resources/js/System/Helpers/MountEntityApp.js`
- Tablas: `roles`, `role_sub_sections`, `role_branches`, `role_cash_registers`, `role_warehouses`, `users`, `sections`, `sub_sections`, `companies_sub_sections`

## Campos necesarios

### `roles`

- `company_id`
- `slug`
- `name`
- `is_full_access`
- `branch_scope_mode`
- `cash_register_scope_mode`
- `warehouse_scope_mode`
- `status`
- auditoria de creacion y actualizacion

### `role_sub_sections`

- `role_id`
- `sub_section_id`
- `actions` JSON
- `status`
- auditoria de creacion y actualizacion

## Reglas

- El perfil pertenece a una sola empresa.
- `name` es unico por empresa en backend.
- `is_full_access = true` representa acceso total a los modulos habilitados para la empresa.
- Si `is_full_access = false`, el perfil depende de `role_sub_sections` y sus acciones.
- Las acciones disponibles son `view`, `create`, `update`, `delete`, `export`, `import` y `operate`.
- Una acción operativa conserva también `view` para que el flujo sea utilizable.
- Los modos `all/restricted` controlan sucursales, cajas y almacenes del perfil.
- Una caja o almacén restringido también debe pertenecer a una sucursal permitida.
- El administrador inicial se crea con acceso total.
- El colaborador hereda permisos por `users.role_id`.
- `CompanySectionService::getSections($companyId, $roleId)` filtra menu por empresa y rol.
- El selector de modulos de Perfiles usa `CompanySectionService::getSections($companyId)`, por lo que respeta el orden custom por empresa: primero `companies_sub_sections.section_order` para cabeceras y luego `companies_sub_sections.sub_section_order` para modulos internos.
- `RolePermissionService` cachea permisos por empresa y rol.
- `module.permission` valida módulo y acción usando nombre de ruta, método HTTP y los mapeos de `config/permissions.php`.
- `resource.scope` valida que los recursos operativos pertenezcan al alcance efectivo del usuario.
- Un colaborador limitado no puede delegar acceso total ni permisos o recursos superiores a los propios.
- Las mutaciones de rol o permisos limpian cache de menu, permisos e `initParams` impactados.

## Flujo UI

- El listado permite buscar perfiles de acceso.
- La modal permite crear o editar nombre, estado, acceso total, módulos, acciones y alcance operativo.
- Los modulos se agrupan por seccion del menu, incluyen buscador interno y permiten ver solo seleccionados para disminuir clics cuando la empresa tenga muchos accesos.
- La interfaz sigue la linea visual de Productos: filtros compactos, tabla `br-entity-table`, modal `br-entity-modal` y clases `br-roles`.

## Mejoras sugeridas

- Bloquear cambios que puedan dejar al usuario actual sin acceso administrativo.
- Agregar auditoria visible de cambios de permisos.
- Permitir duplicar un perfil para empresas con muchas combinaciones.
- Mostrar en Colaboradores un resumen de modulos permitidos por perfil.
