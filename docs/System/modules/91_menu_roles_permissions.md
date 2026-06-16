# 91 - Menu, roles y permisos

## Que hace

Agrupa la estructura de navegacion, habilitacion por empresa y autorizacion funcional por perfil.

## Tablas

- `sections`
- `sub_sections`
- `companies_sub_sections`
- `roles`
- `role_sub_sections`
- `user_preferences`

## Reglas

- `sections` define grupos principales.
- `sub_sections` define items navegables, descripcion y ruta base.
- `companies_sub_sections` habilita modulos por empresa.
- `roles` clasifica usuarios y define si el perfil tiene acceso total con `is_full_access`.
- `role_sub_sections` asigna modulos permitidos a un rol sin acceso total.
- `users.role_id` vincula cada colaborador con su perfil.
- `user_preferences` guarda configuracion personal, pero no concede permisos.
- `CompanySectionService::getSections($companyId)` devuelve el menu habilitado por empresa.
- `CompanySectionService::getSections($companyId, $roleId)` devuelve el menu visible para ese perfil.
- `RolePermissionService` cachea permisos por empresa y rol para validar rutas con rapidez.
- `EnsureModulePermission` protege las rutas internas usando el prefijo de ruta, por ejemplo `products.*`.
- El layout consume `CompanySectionService` y no lee claves de cache directamente.
- `CompanySubSectionObserver` invalida el menu cuando cambia la habilitacion de modulos.
- `RoleObserver` y `RoleSubSectionObserver` invalidan permisos y menu cuando cambia un perfil.

## Caches

- Menu por empresa: `company_sections:company:{companyId}:role:all`.
- Menu por rol: `company_sections:company:{companyId}:role:{roleId}`.
- Permisos por rol: `role_permissions:company:{companyId}:role:{roleId}`.
- InitParams de roles: `RoleConfigService`, invalidado junto con usuarios porque colaboradores consumen roles.

## Buenas practicas

- Al crear un modulo, registrar `sub_sections.dom_route` con el mismo prefijo de las rutas reales.
- No validar permisos solamente en Vue. El backend decide con middleware.
- Mantener la visibilidad de menu y la autorizacion alineadas, pero separadas.
- Si un modulo agrega acciones sensibles, documentarlas para la futura matriz modulo + accion.
- No usar `Cache::flush()` para corregir permisos; invalidar por empresa y recurso.

## Mejoras sugeridas

- Extender la matriz actual de modulo a modulo + accion.
- Agregar policies para acciones sensibles cuando exista granularidad.
- Permitir auditoria y comparacion historica de perfiles.
- Proteger cambios que puedan dejar a todos los administradores sin acceso total.
