# 22 - Colaboradores

## Que hace

Administra usuarios internos de la empresa.

## Archivos

- Ruta: `routes/System/Organizations/User.php`
- Controlador: `UserController`
- Servicios: `UserService`, `UserConfigService`
- Requests: `StoreUserRequest`, `UpdateUserRequest`
- Tablas: `users`, `roles`, `role_sub_sections`, `identity_document_types`, `user_preferences`

## Campos necesarios

- `company_id`
- `role_id`
- `identity_document_type_id`
- `document_number`
- `name`
- `email`
- `password`
- `phone_number`
- `gender`
- `birthdate`
- `status`

## Reglas

- Usuario pertenece a empresa.
- Email debe ser unico segun regla definida.
- Password debe gestionarse con hash.
- `role_id` controla el alcance funcional del colaborador.
- Un rol con `is_full_access = true` puede ingresar a todos los modulos habilitados.
- Un rol sin acceso total queda restringido a las subsecciones activas en `role_sub_sections`.
- La restriccion se aplica en menu, rutas web y respuestas JSON mediante `module.permission`.

## Mejoras sugeridas

- Implementar permisos por modulo + accion cuando se requiera granularidad.
- Agregar bloqueo de usuario sin borrar.
- Agregar cambio de password separado.
- Auditar acciones sensibles por usuario.
