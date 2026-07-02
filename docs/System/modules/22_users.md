# 22 - Colaboradores

## Que hace

Administra usuarios internos de la empresa.

## Archivos

- Ruta: `routes/System/Organizations/User.php`
- Controlador: `UserController`
- Servicios: `UserService`, `UserConfigService`
- Requests: `StoreUserRequest`, `UpdateUserRequest`
- Tablas: `users`, `roles`, `role_sub_sections`, `identity_document_types`, `user_preferences`, `user_branches`, `user_cash_registers`, `user_warehouses`

## Campos necesarios

- `company_id`
- `role_id`
- `branch_scope_mode`
- `cash_register_scope_mode`
- `warehouse_scope_mode`
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
- Un rol sin acceso total queda restringido a las subsecciones y acciones activas en `role_sub_sections`.
- La restricción funcional se aplica en menú, rutas web y respuestas JSON mediante `module.permission`.
- El colaborador puede heredar los alcances del perfil o reducirlos con sucursales, cajas y almacenes específicos.
- La restricción operativa se aplica mediante `resource.scope` y consultas filtradas.
- No se puede asignar un perfil con permisos superiores a los del usuario que realiza la gestión.

## Mejoras sugeridas

- Agregar bloqueo de usuario sin borrar.
- Agregar cambio de password separado.
- Auditar acciones sensibles por usuario.
## Alcance operativo

- Cada colaborador puede restringir sucursales, cajas y almacenes.
- Un selector vacío significa **heredar del perfil**, no acceso total.
- Las selecciones se guardan en `user_branches`, `user_cash_registers` y `user_warehouses`.
- El alcance efectivo es la intersección entre perfil y colaborador.
- En el formulario, los tres selectores aparecen después de **Perfil de acceso** y filtran cajas/almacenes según las sucursales elegidas.
- Crear o editar una sucursal invalida la configuración de Colaboradores, Caja, Ventas, Compras e Inventario para evitar que los selectores mantengan sucursales antiguas.
