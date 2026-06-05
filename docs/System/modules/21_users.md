# 21 - Colaboradores

## Que hace

Administra usuarios internos de la empresa.

## Archivos

- Ruta: `routes/System/Organizations/User.php`
- Controlador: `UserController`
- Servicios: `UserService`, `UserConfigService`
- Requests: `StoreUserRequest`, `UpdateUserRequest`
- Tablas: `users`, `roles`, `identity_document_types`, `user_preferences`

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
- Rol controla alcance funcional, aunque conviene formalizar permisos.

## Mejoras sugeridas

- Definir permisos por rol/subseccion.
- Agregar bloqueo de usuario sin borrar.
- Agregar cambio de password separado.
- Auditar acciones sensibles por usuario.

