# 91 - Menu, roles y permisos

## Que hace

Agrupa la estructura de menu y acceso funcional.

## Tablas

- `sections`
- `sub_sections`
- `companies_sub_sections`
- `roles`
- `user_preferences`

## Reglas

- `sections` define grupos principales.
- `sub_sections` define items navegables y rutas.
- `companies_sub_sections` habilita modulos por empresa.
- `roles` clasifica usuarios.
- `user_preferences` guarda configuracion personal.
- `CompanySectionService` consulta y cachea únicamente los campos necesarios para navegación.
- `CompanySubSectionObserver` invalida el menú cuando cambia la habilitación de módulos.
- El layout consume el servicio; no accede directamente a claves de caché.

## Mejoras sugeridas

- Formalizar matriz de permisos por rol y subseccion.
- Separar visibilidad de menu de autorizacion real.
- Agregar middleware/policies para acciones sensibles.
- Crear una pantalla de permisos cuando se active administracion granular.
