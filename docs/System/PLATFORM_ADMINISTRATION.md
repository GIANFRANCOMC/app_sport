# Administración SaaS (`app.gympe.test`)

## Objetivo

El subdominio `app` administra el registro landlord, los estados de los clientes tenant, los módulos habilitados y los avisos. Está aislado de los usuarios y bases de datos tenant.

La interfaz autenticada utiliza un único shell Vue. Cambiar entre el listado y el detalle actualiza `history.pushState` y consulta JSON; no vuelve a descargar el layout, las fuentes, Bootstrap ni el bundle de la aplicación.

## Arquitectura

### Capa de presentación

- `resources/views/Platform/layouts/app.blade.php`: documento HTML compartido por login y shell.
- `resources/views/Platform/shell.blade.php`: único punto de montaje autenticado y configuración segura serializada con `Js::from`.
- `resources/js/Platform/App.vue`: cabecera, navegación local, notificaciones y cierre de sesión.
- `resources/js/Platform/pages/TenantIndex.vue`: filtros, paginación, indicadores y aprovisionamiento.
- `resources/js/Platform/pages/TenantDetail.vue`: estado, módulos y avisos.
- `resources/js/Platform/api.js`: cliente Axios con CSRF, cabeceras JSON y mensajes normalizados.

Las vistas Blade anteriores de listado y detalle se eliminaron. Ninguna página autenticada debe agregar un nuevo `@vite` propio: las funcionalidades deben incorporarse al entry `resources/js/Platform/app.js` y dividirse en componentes.

### Rutas de página

- `GET /tenants`: carga el shell.
- `GET /tenants/{tenant}`: carga el mismo shell y permite acceso directo o recarga segura del detalle.

### API web autenticada

- `GET /api/tenants`: listado paginado, búsqueda y conteos.
- `POST /api/tenants`: aprovisionamiento.
- `GET /api/tenants/{tenant}`: detalle compuesto.
- `PATCH /api/tenants/{tenant}/status`: estado operativo.
- `PUT /api/tenants/{tenant}/modules`: módulos mediante escritura masiva.
- `POST /api/tenants/{tenant}/announcements`: publicación de aviso.
- `PATCH /api/tenants/{tenant}/announcements/{announcement}`: activación o desactivación.

Estas rutas pertenecen al grupo `web` del dominio de plataforma. Conservan sesión, CSRF, `platform.auth`, model binding y límites de frecuencia. No se exponen mediante el API público del proyecto.

## Backend y rendimiento

El listado selecciona únicamente las columnas visibles, pagina desde SQL y limita `per_page` a 50. La búsqueda por prefijo cubre slug, base de datos y dominio para poder aprovechar índices; los caracteres comodín se escapan. Los conteos se calculan con una agregación por estado.

El detalle devuelve módulos y como máximo los 50 avisos más recientes. La conexión dinámica al tenant se abre solo mientras se consultan o actualizan sus módulos y siempre se libera en `finally`.

La actualización de módulos genera el estado completo en memoria y ejecuta un único `upsert`. Antes se ejecutaba un `updateOrInsert` por cada módulo, por lo que el tiempo crecía linealmente con el menú.

El aprovisionamiento está encapsulado en `PlatformTenantProvisioner`; el controlador no administra conexiones ni comandos. La solicitud es asíncrona desde el navegador y mantiene un estado visual de progreso. El comando continúa siendo la única implementación del proceso de creación para evitar dos flujos de negocio diferentes.

La creación se realiza desde un modal bloqueante. Mientras el backend prepara la base tenant, la interfaz impide cerrar el modal, repetir el envío o abandonar accidentalmente la página. El backend añade un bloqueo exclusivo por `slug` durante 15 minutos, conserva el `throttle` de aprovisionamiento y valida subdominio, documento y una contraseña robusta. Los intentos correctos y fallidos quedan registrados en `tenant_audit_logs` sin almacenar credenciales.

Los módulos habilitados usan un control visual propio, accesible mediante teclado y con un check de alto contraste. El mismo patrón se reutiliza para la propiedad **Descartable** de los avisos. La acción de cada fila se denomina **Configurar**, y el cierre de sesión se presenta explícitamente como una acción destructiva.

## Módulos iniciales de una organización

`sub_sections.is_enabled_by_default` es la fuente de verdad persistida. `SystemNavigationSeeder` define el valor y `SystemCatalogSyncService` lo proyecta a `companies_sub_sections` al crear la empresa. La plataforma puede activar o desactivar posteriormente cualquier módulo mediante su escritura masiva.

Los siguientes módulos nacen desactivados:

- Dashboard y Reportes.
- POS restaurante.
- Historial, Membresías, Asistencias de clientes, Notificaciones y Libro de reclamaciones del mundo Clientes.
- Membresías y Recetas y platillos del Catálogo comercial.
- Activos, Gestión de activos, Dispositivos biométricos y Asistencia del personal de Mi organización.

El rol administrador conserva el catálogo completo de permisos, pero la autorización siempre intersecta esos permisos con los módulos habilitados para la empresa. Por ello, un administrador tenant no puede abrir por URL directa un módulo que la plataforma haya desactivado. Al cambiar módulos se invalidan tanto la caché de navegación como la de autorización.

## Migraciones e impacto

Como el proyecto todavía se encuentra en etapa descartable y la base será recreada, los cambios se consolidaron en las migraciones fuente:

- `tenant_databases.database_name` ahora es único. Una base física no puede pertenecer a dos tenants.
- Índice de `tenant_databases(status, slug)` para listado y filtro.
- Índice de dominios por tenant, estado y principal.
- Índices de auditoría por tenant/fecha y acción/resultado/fecha.
- `companies_sub_sections(company_id, sub_section_id)` ahora es único, requisito del `upsert`.
- Índice de navegación de módulos por empresa, estado y orden.
- `sub_sections.is_enabled_by_default` concentra la política inicial de activación y evita listas duplicadas en controladores o componentes.

Impacto al aplicar sobre datos no descartables: primero debe comprobarse que no existan bases repetidas ni filas duplicadas en `companies_sub_sections`. En el flujo actual se espera recrear las bases, por lo que no se agregó una migración de limpieza que pudiera decidir arbitrariamente qué registro conservar.

`LandlordSchemaService` ya no crea un subconjunto de tablas durante la ejecución. Solo valida el esquema y solicita ejecutar `php artisan platform:install` si falta alguna tabla. Así, las migraciones landlord son la única fuente de verdad.

La antigua migración correctiva `2026_06_26_000002_harden_tenant_registry` se consolidó en la migración inicial landlord. En una instalación nueva ya no existían las columnas sensibles ni los dominios personalizados que aquella migración intentaba corregir, por lo que mantenerla solo agregaba una ejecución vacía y dos fuentes históricas para el mismo esquema.

## Instalación limpia

```bash
php artisan platform:install
php artisan tenant:create demo --admin-password="una-clave-segura"
```

`platform:install` ejecuta todas las migraciones de `database/migrations/landlord` y crea el administrador configurado. Debe ejecutarse antes de cualquier `tenant:create`.

## Convenciones futuras

1. Una nueva pantalla de plataforma debe ser un componente del shell, no un Blade con otro entry Vite.
2. Los listados deben paginar y seleccionar solo las columnas visibles.
3. Las acciones deben devolver JSON y mantener un estado de carga local.
4. Las escrituras por colección deben usar operaciones masivas con una restricción única coherente.
5. La base landlord nunca se modifica dinámicamente fuera de migraciones.
6. Toda consulta a una base tenant debe liberar la conexión en `finally`.
7. El acceso directo y los botones atrás/adelante del navegador deben seguir funcionando.

La prueba `PlatformAdministrationArchitectureTest` protege el shell único, la escritura masiva y la fuente única de migraciones.

## Validación realizada

- Migración landlord ejecutada desde cero en `gympe_landlord_testing` y seeder completado.
- Pruebas focalizadas de navegación y aprovisionamiento: 15 pruebas aprobadas, incluidas la política de módulos iniciales, la imposibilidad de omitirla por URL, el bloqueo de aprovisionamiento y el modal bloqueante.
- Sintaxis PHP y formato Pint aprobados en todos los archivos PHP modificados.
- Compilación Vite de producción aprobada, incluidos los entries independientes de Compras y la interfaz de plataforma.
- Prueba HTTP real sobre `app.gympe.test`: shell 200, listado JSON 200 y detalle JSON 200.
- En ejecución local caliente: listado cercano a 1.0 s, primer detalle 1.1 s y segundo detalle 0.76 s incluyendo todo el ciclo HTTP de Laragon. La consulta directa de los 49 módulos toma aproximadamente 71 ms; el resto corresponde al arranque y middleware del entorno local.

El beneficio principal de navegación se percibe después del primer shell: al cambiar de listado a detalle ya no se vuelven a descargar ni parsear HTML global, fuentes, Bootstrap, CSS y JavaScript compartidos.
