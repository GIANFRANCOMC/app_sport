# Arquitectura y convenciones tecnicas

## Estructura general

El proyecto usa Laravel como backend principal y monta experiencias Vue en paginas Blade. La estructura esta dividida en dos grandes ambitos:

- `System`: area interna autenticada.
- `Guest`: area publica por empresa.

Carpetas clave:

- `app/Http/Controllers/System`: controladores internos por modulo.
- `app/Http/Controllers/Guest`: controladores publicos.
- `app/Services/System`: reglas de negocio y datos iniciales.
- `app/Models/System`: modelos internos.
- `app/Models/Guest`: modelos para consultas publicas.
- `app/Http/Requests/System`: validaciones de formularios internos.
- `routes/System`: rutas por modulo interno.
- `routes/Guest`: rutas publicas por modulo.
- `resources/views/System`: contenedores Blade internos.
- `resources/views/Guest`: contenedores Blade publicos.
- `resources/js/System`: componentes, helpers y paginas Vue internas.
- `resources/js/Guest`: componentes, helpers y paginas Vue publicas.

## Rutas

`routes/web.php` agrupa todo bajo middleware `web`.

Rutas publicas:

- `{company_slug}/book_complaints`
- `{company_slug}/home`
- `{company_slug}/tracking_attendances`
- `{company_slug}/biometric_devices`

Todas usan `company.exists`, lo que indica que la empresa se resuelve por slug antes de entrar al controlador.

Rutas internas:

- Protegidas por `auth` y `verified`.
- Se agrupan por prefijos como `/customers`, `/sales`, `/branches`, `/dashboard`.
- Cada archivo de `routes/System` define el CRUD o acciones especificas del modulo.

## Patron de controlador interno

La mayoria de controladores internos repiten este flujo:

- `initParams(Request $request)`: devuelve configuracion inicial para la pagina Vue.
- `list(Request $request)`: devuelve registros paginados con filtros.
- `index()`: retorna la vista Blade principal.
- `create()`, `edit()`: a veces vacios o placeholders.
- `store(FormRequest|Request $request)`: crea registros.
- `show(Model $record|int $id)`: devuelve un registro.
- `update(FormRequest|Request $request, int $id)`: actualiza.
- `destroy(Model $record)`: elimina o inactiva.

Los controladores internos suelen extender `BaseController`, que concentra:

- `getAuthUser()`
- `getCompanyId()`
- `getUserId()`
- `getPerPage()`
- `getFilters()`
- `getPage()`

Tambien usan traits/concerns para respuestas API y manejo de excepciones:

- `HandlesApiResponses`
- `HandlesExceptions`

## Patron de servicio

Existen dos familias de servicios:

- Servicios de configuracion (`*ConfigService`): preparan datos para `initParams`, usualmente con cache.
- Servicios de negocio (`*Service`, `*BusinessService`): crean, actualizan, cancelan o consultan entidades.

Ejemplos:

- `ProductConfigService` y `ProductService`.
- `TrackingAttendanceConfigService`, `TrackingAttendanceService` y `TrackingAttendanceBusinessService`.
- `SaleConfigService` y `SaleService`.
- `BiometricDeviceConfigService` y `BiometricDeviceService`.

## Frontend

Cada modulo interno suele tener:

- Vista Blade: `resources/views/System/general/<Module>/<page>/main.blade.php`.
- Entrada JS: `resources/js/System/Pages/<Module>/<page>/main.js`.
- Componente Vue: `resources/js/System/Pages/<Module>/<page>/main.vue`.

Hay componentes y helpers compartidos:

- Componentes de formulario: `InputText`, `InputDate`, `InputSelect`, `InputNumber`, etc.
- Componentes genericos: `DataTable`, `FiltersSection`, `FormModal`, `StatusBadge`.
- Helpers CRUD: `BaseCrudModule.js`, `CrudMixin.js`, `ModuleFactory.js`.
- Helpers de request, fechas, numeros, strings y validaciones.

## Base de datos

Las migraciones principales inicializan grupos funcionales:

- `create_init_masters_table`: maestros, empresas, secciones, roles, usuarios.
- `create_init_companies_table`: sucursales, series, items, activos, categorias, clientes, almacenes.
- `create_init_sales_table`: ventas.
- `create_init_subscriptions_table`: membresias, asistencias, emails de suscripcion.
- `create_init_clients_table`: libro de reclamaciones.
- `create_init_biometrics_table`: dispositivos biometricos y huellas.

## Multiempresa

El sistema depende fuertemente de `company_id`. Al modificar codigo, revisar:

- Que consultas internas filtren por empresa del usuario autenticado.
- Que ids recibidos por request no permitan acceder a datos de otra empresa.
- Que las rutas publicas usen la empresa cargada por middleware y no datos arbitrarios.
- Que entidades por sucursal validen que la sucursal pertenece a la empresa.

## Cache

Los servicios `*ConfigService` construyen claves por empresa y pagina. Al modificar catalogos o datos de configuracion, considerar limpiar cache del modulo afectado.

## Auditoria

Muchas tablas tienen campos:

- `created_by`
- `updated_by`
- `deleted_by`
- `canceled_by`

Al crear nuevas operaciones, conservar este estilo. Si se cancela o retira algo, no asumir borrado fisico salvo que el modulo ya lo haga.

## Validaciones

Hay `FormRequest` para muchos CRUDs internos. Cuando se agreguen campos a un modulo existente, revisar:

- `Store*Request`
- `Update*Request`
- formulario Vue
- servicio/controlador
- migracion/modelo
- listado o detalle

## Riesgos tecnicos observados

- Algunos metodos CRUD estan vacios o son placeholders en modulos de tracking y publicos.
- Hay strings de negocio escritos directamente en servicios/controladores.
- Hay textos con codificacion rota en archivos y respuestas.
- Algunos servicios usan `Auth::user()` dentro de la capa de servicio, lo que reduce testabilidad.
- Varias operaciones criticas no tienen pruebas visibles.
- El API publico `routes/api.php` esta practicamente vacio; los endpoints publicos estan en web routes.

## Mejoras sugeridas

- Crear una capa de autorizacion/policies para operaciones por empresa y sucursal.
- Mover mensajes de negocio a archivos de traduccion o constantes.
- Convertir estados repetidos a enums o value objects.
- Agregar tests de feature para cada flujo principal.
- Estandarizar todos los servicios para recibir `companyId` y `userId` explicitamente, evitando depender de `Auth` dentro del servicio.
- Documentar y probar invalidacion de cache por modulo.
- Revisar si todos los endpoints que modifican datos usan `FormRequest`.

