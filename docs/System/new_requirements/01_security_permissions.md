# 01 - Seguridad y permisos

## Problema

System depende de `company_id`, sucursales y rutas autenticadas. Hay validaciones distribuidas en controladores y servicios, pero conviene formalizar el criterio.

## Requerimientos sugeridos

- Crear policies o servicios de autorizacion por empresa/sucursal.
- Separar visibilidad de menu de permiso real.
- Validar ownership en todos los endpoints que reciben ids.
- Agregar tests de acceso cruzado entre empresas.

## Impacto

Alto. Toca controladores, servicios y posiblemente middleware.

## Pendientes y mejoras por realizar

- Evolucionar permisos desde modulo hacia modulo + accion.
- Documentar restricciones por sucursal, caja y almacen cuando se completen en usuarios/perfiles.
