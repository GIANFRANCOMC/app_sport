# 01 - Seguridad y permisos

## Estado

Implementado en julio de 2026.

System separa tres decisiones de seguridad:

1. La empresa habilita módulos mediante `companies_sub_sections`.
2. El perfil concede módulos y acciones mediante `role_sub_sections`.
3. El perfil y el colaborador delimitan sucursales, cajas y almacenes.

Ocultar una opción del menú no reemplaza la autorización. Las rutas protegidas usan `module.permission` y `resource.scope`; ambos middleware responden `403` cuando la operación no está permitida.

## Permisos por módulo y acción

`role_sub_sections.actions` guarda un arreglo JSON con acciones permitidas por módulo:

- `view`: consultar páginas, listados y detalles.
- `create`: registrar información.
- `update`: modificar registros.
- `delete`: eliminar cuando el módulo lo admita.
- `export`: descargar reportes o archivos.
- `import`: realizar cargas masivas.
- `operate`: ejecutar ventas POS, movimientos, cierres, recepciones o traslados.

`null` conserva compatibilidad con perfiles anteriores y equivale a todas las acciones. Toda acción operativa implica `view`, porque un usuario no debe operar un módulo que no puede consultar.

`RolePermissionService` resuelve la acción usando el nombre de ruta y el método HTTP. `config/permissions.php` documenta las acciones, los tokens de ruta y los endpoints compartidos por Inventario, Caja y Ventas. Venta POS envía `source_channel=pos` para diferenciar su confirmación de la venta convencional.

## Alcances operativos

### Perfil

Cada perfil define por recurso:

- `all`: todos los recursos de la empresa.
- `restricted`: únicamente los registros vinculados en `role_branches`, `role_cash_registers` o `role_warehouses`.

### Colaborador

Cada colaborador define por recurso:

- `inherit`: conserva el alcance del perfil.
- `restricted`: reduce el alcance mediante `user_branches`, `user_cash_registers` o `user_warehouses`.

El alcance efectivo es la intersección entre perfil y colaborador. Un colaborador nunca amplía el perfil. Las cajas y almacenes también quedan limitados por las sucursales efectivas.

El perfil con `is_full_access = true` omite estas restricciones y representa al administrador de la empresa.

## Aplicación

- Los `initParams` de Ventas, Compras, Caja e Inventario solo entregan recursos permitidos.
- Los listados y exportaciones reutilizan consultas filtradas por alcance.
- `EnsureOperationalScope` valida IDs enviados en request, sesiones de caja, series de comprobantes y documentos consultados por ruta.
- Cambiar el perfil o los alcances invalida únicamente los permisos y configuraciones cacheadas de los usuarios afectados.
- Un usuario limitado no puede crear acceso total, conceder módulos/acciones que no posee, asignar un perfil superior ni ampliar alcances operativos.

## Tablas

- `roles`, `role_sub_sections`
- `role_branches`, `role_cash_registers`, `role_warehouses`
- `users`, `user_branches`, `user_cash_registers`, `user_warehouses`

Todas las tablas se aíslan por `company_id` y conservan claves foráneas hacia sus recursos.

## Mejoras futuras

- Auditoría visible de cambios de perfil, acción y alcance.
- Policies específicas para recursos con reglas de negocio adicionales.
- Pruebas automatizadas de acceso cruzado entre empresas, sucursales y almacenes cuando se estabilice la suite de seguridad.
