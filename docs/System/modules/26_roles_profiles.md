# 26 - Perfiles de acceso

## Que hace

Administra perfiles de acceso para colaboradores de una empresa. Tecnicamente se almacenan en `roles`, pero en la interfaz se nombran como perfiles para que el usuario entienda que esta definiendo el alcance de cada colaborador.

En esta etapa el permiso es por modulo: si el perfil no tiene una subseccion asignada, el colaborador no ve el acceso en el menu y tampoco puede ingresar por URL directa.

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
- Tablas: `roles`, `role_sub_sections`, `users`, `sections`, `sub_sections`, `companies_sub_sections`

## Campos necesarios

### `roles`

- `company_id`
- `slug`
- `name`
- `is_full_access`
- `status`
- auditoria de creacion y actualizacion

### `role_sub_sections`

- `role_id`
- `sub_section_id`
- `status`
- auditoria de creacion y actualizacion

## Reglas

- El perfil pertenece a una sola empresa.
- `name` es unico por empresa en backend.
- `is_full_access = true` representa acceso total a los modulos habilitados para la empresa.
- Si `is_full_access = false`, el perfil depende de `role_sub_sections`.
- El administrador inicial se crea con acceso total.
- El colaborador hereda permisos por `users.role_id`.
- `CompanySectionService::getSections($companyId, $roleId)` filtra menu por empresa y rol.
- El selector de modulos de Perfiles usa `CompanySectionService::getSections($companyId)`, por lo que respeta el orden custom por empresa: primero `companies_sub_sections.section_order` para cabeceras y luego `companies_sub_sections.sub_section_order` para modulos internos.
- `RolePermissionService` cachea permisos por empresa y rol.
- `module.permission` valida el prefijo de ruta en backend para web y JSON.
- Las mutaciones de rol o permisos limpian cache de menu, permisos e `initParams` impactados.

## Flujo UI

- El listado permite buscar perfiles de acceso.
- La modal permite crear o editar nombre, estado, acceso total y modulos asignados.
- Los modulos se agrupan por seccion del menu, incluyen buscador interno y permiten ver solo seleccionados para disminuir clics cuando la empresa tenga muchos accesos.
- La interfaz sigue la linea visual de Productos: filtros compactos, tabla `br-entity-table`, modal `br-entity-modal` y clases `br-roles`.

## Mejoras sugeridas

- Extender el modelo a modulo + accion (`ver`, `crear`, `editar`, `anular`, `exportar`).
- Bloquear cambios que puedan dejar al usuario actual sin acceso administrativo.
- Agregar auditoria visible de cambios de permisos.
- Permitir duplicar un perfil para empresas con muchas combinaciones.
- Mostrar en Colaboradores un resumen de modulos permitidos por perfil.
