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

Las mutaciones nuevas de entidades con `company_id` deben extender `CompanyFormRequest`. Este contrato:

- Autoriza únicamente usuarios con empresa válida.
- Permite normalizar cadenas antes de ejecutar reglas.
- Evita repetir autorización básica en cada Store/Update Request.

`BelongsToCompany` valida relaciones directas y también admite joins para entidades cuya empresa se obtiene de otra tabla. Productos lo usa para categorías, marcas y almacenes; en almacenes llega a `branches.company_id` mediante join.

La validación HTTP no reemplaza las restricciones de base de datos ni las comprobaciones del servicio. Para relaciones sensibles se aplican tres niveles: FormRequest, defensa de negocio en Service y claves/índices en migración.

## Estados

Estados observados:

- Generales: `active`, `inactive`.
- Ventas: `active`, `canceled`, `inactive`.
- Asistencias: `active`, `canceled`, `inactive`, `finalized`.
- Reclamaciones: `pending`, `in_progress`, `resolved`.
- Emails: `pending`, `sent`, `failed`.
- Activos asignados: `active`, `maintenance`, `retired`.

## Cache

Todos los servicios `*ConfigService` heredan de `BaseConfigService`.

- La clave incluye módulo, empresa y página: `init_params:{modulo}:company:{id}:page:{page}`.
- El TTL predeterminado es una hora.
- Cada servicio implementa únicamente `getCachePrefix()` y `buildConfig()`.
- Los módulos con más de una página declaran `cachePages()`; actualmente Ventas usa `main` y `list`.
- Una página vacía o desconocida se normaliza a la primera página soportada.
- `clearAllCache($companyId)` elimina todas las páginas declaradas por el módulo.
- `InitParamsCacheInvalidationService` resuelve dependencias entre recursos y módulos consumidores.
- No se invalida caché cuando una mutación no modifica datos incluidos en `initParams`.

Los maestros globales activos se reutilizan durante seis horas mediante `MasterReferenceDataService`. Si monedas o tipos de documento adquieren mantenimiento CRUD, la mutación debe ejecutar `MasterReferenceDataService::clearCache()`.

## Configuración por empresa

`company_settings` concentra valores configurables que pertenecen a una empresa y que no justifican una tabla funcional independiente.

- Cada valor se identifica por `company_id`, `group` y `key`.
- `value_type` permite interpretar strings, booleanos, enteros, decimales o JSON.
- El grupo inicial `internal_code_prefixes` define prefijos para productos, servicios, membresías, marcas, categorías, sucursales y activos.
- `CompanySettingService` entrega valores por grupo y mantiene defaults de compatibilidad.
- `BaseConfigService::internalCodePrefixes()` expone el mismo contrato a los módulos que lo requieren.
- `InternalCodeService` es la autoridad para aplicar el prefijo en backend. La presentación Vue no reemplaza esta validación.
- `AppliesInternalCodePrefix` evita repetir la preparación del código en los FormRequests equivalentes.
- Un valor nulo o vacío desactiva el prefijo para esa entidad y empresa.

La tabla está preparada para futuras reglas empresariales, como permitir ventas con stock negativo. La interfaz administrativa permanece pendiente hasta definir permisos, auditoría e invalidación de caché.

## Errores de formulario

- Los mensajes inline deben describir únicamente la corrección, por ejemplo `Campo obligatorio.`.
- Los resúmenes de modal o SweetAlert deben añadir el label, por ejemplo `Precio de venta: Campo obligatorio.`.
- `Forms.getDescriptiveErrors` resuelve el contexto frontend.
- `Forms.handleFormResponseErrors` conserva el error bajo el campo y genera un resumen contextual para respuestas backend, incluidos los `422` de FormRequest.

## Datos de referencia para `initParams`

Los modelos no deben exponer métodos genéricos como `getAll($type, $companyId)`. Ese contrato ocultaba filtros tras strings, repetía el identificador de empresa y permitía combinaciones inválidas.

- `CompanyReferenceDataService::for($companyId)` concentra consultas acotadas a una empresa.
- Sus métodos expresan la intención: `brands()`, `categories()`, `stockWarehouses()`, `branchesWithSeries()`, `activeCustomers()`, `saleItems()`, entre otros.
- `MasterReferenceDataService` entrega maestros globales activos, como monedas y tipos de documento según su uso.
- Cada `ConfigService` crea una referencia por empresa y reutiliza esa instancia dentro de su construcción de `initParams`.
- Los modelos conservan relaciones, accessors, scopes y reglas propias de la entidad; no conocen el contexto de una pantalla.
- La prueba `ModelGetAllConventionTest` evita reintroducir `Model::getAll(...)`.

## Menú por empresa

`CompanySectionService` consulta y almacena las secciones habilitadas por `companies_sub_sections`.

- El layout solicita las secciones al servicio; no lee claves de caché directamente.
- La clave es `company_sections:company:{id}` y su TTL es de 30 minutos.
- La consulta selecciona únicamente los campos requeridos por sidebar, favoritos y Home.
- `CompanySubSectionObserver` invalida automáticamente la empresa afectada al crear, editar o eliminar una asignación.
- `Company` ya no contiene `getActiveSections`; la consulta pertenece al servicio que conoce su uso y caché.
- No se utiliza un listener de autenticación para precargar el menú.

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
