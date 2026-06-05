# System - Arquitectura

## Proposito

System es una aplicacion Laravel 10 con frontend Vue 3 montado sobre vistas Blade. Esta orientada a usuarios internos de una empresa. Cada usuario autenticado pertenece a una empresa mediante `company_id`, y la mayoria de operaciones deben quedar acotadas a esa empresa.

## Capas

- Rutas: archivos por modulo en `routes/System`.
- Controladores: reciben requests, preparan filtros, validan ownership basico y delegan a servicios.
- FormRequests: validan creacion/actualizacion en modulos CRUD.
- Servicios: concentran reglas de negocio, consultas paginadas, transacciones y cache de parametros.
- Modelos: relaciones, accessors, scopes y helpers de entidad.
- Blade: contenedor inicial de cada pantalla.
- Vue: experiencia interactiva de listados, formularios, modales y acciones.

## Patron comun de modulo

Un modulo System normalmente tiene:

- Ruta con prefijo: `/customers`, `/sales`, `/branches`, etc.
- Controlador: `*Controller`.
- Servicio principal: `*Service`.
- Servicio de configuracion: `*ConfigService`.
- Request de creacion y actualizacion si modifica datos.
- Modelo o modelos asociados.
- Vista Blade en `resources/views/System/general`.
- Pagina Vue en `resources/js/System/Pages`.

## Multiempresa

Regla fuerte: toda consulta operativa debe filtrar por `company_id` o validar que la entidad pertenece a una sucursal/serie/empresa del usuario autenticado.

Cuando se reciba un id por request:

- Validar empresa directa si la tabla tiene `company_id`.
- Validar sucursal si la tabla depende de `branch_id`.
- Validar serie mediante su sucursal si la venta usa `serie_id`.
- Evitar confiar en ids enviados por frontend.

## Estados

Estados observados:

- Generales: `active`, `inactive`.
- Ventas: `active`, `canceled`, `inactive`.
- Asistencias: `active`, `canceled`, `inactive`, `finalized`.
- Reclamaciones: `pending`, `in_progress`, `resolved`.
- Emails: `pending`, `sent`, `failed`.
- Activos asignados: `active`, `maintenance`, `retired`.

## Cache

Los servicios `*ConfigService` suelen preparar datos de selects, estados y registros iniciales. Cuando se modifiquen catalogos, sucursales, usuarios o configuraciones usadas por initParams, revisar si debe limpiarse cache del modulo.

## Riesgos actuales

- Algunos servicios dependen de `Auth::user()` internamente; esto funciona, pero complica pruebas.
- Existen mensajes en codigo con problemas de codificacion de caracteres.
- Algunos endpoints usan `Request` directo donde seria mejor `FormRequest`.
- Hay acciones criticas sin pruebas automatizadas visibles.
- Algunas relaciones por empresa/sucursal se validan en servicios, otras en controlador; conviene estandarizar.

## Criterio para evolucionar

No se recomienda reescribir toda la arquitectura. El criterio adecuado es mejorar por flujo:

- Mantener patron actual si el cambio es pequeno.
- Extraer servicios compartidos si hay duplicacion real.
- Introducir tests en flujos criticos antes de cambiar reglas sensibles.
- Mejorar autorizacion y validacion sin romper la estructura existente.

