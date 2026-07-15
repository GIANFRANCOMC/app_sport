# System - Arquitectura multi-tenant por subdominio

## Resumen

Gympe atiende exclusivamente subdominios registrados, por ejemplo `demo.gympe.test` o `cliente.app.ejemplo.com`. El dominio raíz pertenece a otro proyecto y este código no lo atiende.

Cada tenant dispone de:

- un subdominio único de un solo nivel;
- una base de datos MySQL propia;
- usuarios, sesiones, caché lógica y datos operativos aislados;
- un `company_id` interno que permite empresa raíz y subcompañías dentro de la misma BD tenant;
- almacenamiento con prefijo `tenants/{slug}` para impedir colisiones entre clientes.

No se aceptan IPs, `localhost`, el dominio raíz, hosts desconocidos ni subdominios reservados. La solicitud se rechaza antes de iniciar sesión o consultar modelos tenant.

## Conexiones

### Landlord

La conexión `landlord` contiene únicamente el registro central:

- `tenant_databases`: slug, empresa raíz esperada, nombre de BD, estado y última resolución;
- `tenant_domains`: host exacto y activo asociado al tenant;
- `tenant_audit_logs`: altas, cambios de estado, verificaciones, rechazos de host y resultados operativos.

Landlord no almacena host, usuario ni contraseña de MySQL. Las credenciales pertenecen al entorno seguro del servidor y nunca deben aparecer en Git, tablas, logs o respuestas HTTP.

### Tenant

La conexión `tenant` reutiliza host, puerto y credenciales del entorno y cambia únicamente el nombre de base. `TenantConnectionManager` valida el formato del nombre y exige `TENANT_DB_PREFIX` cuando `TENANT_ENFORCE_DB_PREFIX=true`.

## Resolución HTTP

Orden de defensas:

1. `TrustProxies` interpreta proxy y HTTPS solo desde proxies permitidos.
2. `TrustHosts` acepta exclusivamente `<slug>.<TENANCY_BASE_DOMAIN>`.
3. `ResolveTenant` exige subdominio válido, registrado y activo.
4. Se activa la conexión tenant antes de sesión, autenticación y rutas.
5. `EnsureTenantSession` comprueba que la sesión pertenezca al mismo `tenant_database_id`.
6. `EnsureAuthenticatedSession` valida estado del usuario y `session_version`.
7. `SecurityHeaders` protege navegador y evita cachear HTML autenticado.

El resolver usa una clave única `tenancy:resolver:<sha256(host)>` durante `TENANCY_RESOLVER_CACHE_SECONDS`. Los comandos de alta, suspensión, reactivación y limpieza invalidan esa misma clave; no existen claves versionadas paralelas.

Los intentos contra hosts desconocidos se registran en landlord con deduplicación breve por host e IP. El registro de auditoría nunca puede convertir un rechazo seguro en un error 500.

## Configuración

```env
LANDLORD_DB_CONNECTION=landlord
LANDLORD_DB_HOST=127.0.0.1
LANDLORD_DB_PORT=3306
LANDLORD_DB_DATABASE=gympe_landlord
LANDLORD_DB_USERNAME=usuario_landlord
LANDLORD_DB_PASSWORD=secreto_externo

TENANT_DB_CONNECTION=tenant
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=usuario_runtime_tenant
TENANT_DB_PASSWORD=secreto_externo
TENANT_DB_PREFIX=gympe_tenant_
TENANT_ENFORCE_DB_PREFIX=true

TENANCY_BASE_DOMAIN=gympe.test
TENANCY_ENFORCE_SUBDOMAINS=true
TENANCY_RESERVED_SUBDOMAINS=www,api,admin,mail,static,assets
TENANCY_RESOLVER_CACHE_SECONDS=60
TENANT_SESSION_COOKIE_PREFIX=gympe_tenant
```

`SESSION_DOMAIN` debe permanecer vacío. Un dominio compartido, como `.gympe.test`, compartiría cookies entre clientes y está prohibido.

## Provisionamiento

Preparar landlord:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord --force
```

Crear un tenant local:

```bash
php artisan tenant:create demo \
  --commercial-name="Demo Gym" \
  --legal-name="Demo Gym S.A.C." \
  --document-number=20600000001
```

En producción, infraestructura debe crear previamente la BD y conceder al usuario runtime solo permisos operativos:

```bash
php artisan tenant:create cliente \
  --database=gympe_tenant_cliente \
  --skip-create-database \
  --commercial-name="Cliente" \
  --legal-name="Cliente S.A.C." \
  --document-number=20600000010
```

## Operación

```bash
php artisan tenant:list
php artisan tenant:list --status=active
php artisan tenant:health
php artisan tenant:health demo
php artisan tenant:suspend demo --force
php artisan tenant:suspend demo --activate --force
php artisan tenant:cache-clear demo
php artisan tenant:cache-clear
```

- `tenant:list` consulta landlord sin abrir las bases tenant.
- `tenant:health` prueba conexión, consulta básica, tabla `companies` y latencia.
- `tenant:suspend` y `--activate` actualizan tenant y dominio en una transacción, invalidan caché y auditan la acción.
- `tenant:cache-clear` invalida una clave o todas las claves conocidas sin limpiar la caché completa de la aplicación.

El scheduler ejecuta comandos tenant-aware que iteran tenants activos, conectan y desconectan cada BD en `try/finally`, y auditan el resultado. Actualmente cubre notificaciones de membresías, vencimiento de membresías, cierre técnico de asistencias de clientes y retención de historial.

En servidor debe configurarse un solo cron de Laravel:

```bash
* * * * * cd /ruta/al/gympe && php artisan schedule:run >> /dev/null 2>&1
```

Comandos programados:

- `notifications:send-subscriptions --limit=100`: procesa correos pendientes cada cinco minutos.
- `subscriptions:cancel-expired`: inactiva membresías vencidas cada hora.
- `attendances:close-stale-customers --limit=500`: cierra asistencias antiguas sin salida cada hora.
- `attendances:prune-customers --limit=1000`: depura historial elegible diariamente a las 03:20.

Los jobs futuros deben declarar `UseTenantConnection`; un job sin contexto tenant no debe consultar modelos operativos.

## Demostración

```bash
php artisan db:seed --class=LandlordTenantDemoSeeder --force
```

| Cliente | Subdominio | Base de datos |
| --- | --- | --- |
| Demo Gym | `demo.gympe.test` | `gympe_tenant_demo` |
| Andina Fitness | `andina.gympe.test` | `gympe_tenant_andina` |
| Fit Center | `fitcenter.gympe.test` | `gympe_tenant_fitcenter` |

## Reglas obligatorias

- Nunca confiar en un `company_id` recibido del frontend.
- Nunca resolver un tenant solo por slug; host, dominio y estados deben coincidir.
- Nunca guardar credenciales en `tenant_databases`.
- Nunca habilitar `SESSION_DOMAIN` compartido.
- Comandos, scheduler y jobs deben activar explícitamente el tenant.
- Archivos deben resolverse mediante `TenantStoragePath` o un disco equivalente por tenant.
- Backups, restauraciones y eliminación de datos se ejecutan por BD tenant.

## Evolución de infraestructura

Estas tareas no se resuelven dentro del repositorio y deben formar parte del plan operativo del proveedor cloud:

- rotación automática de credenciales y secretos;
- backups cifrados por tenant, retención y prueba periódica de restauración;
- alertas centralizadas sobre fallos de salud, hosts rechazados y acciones landlord;
- consola landlord desplegada en un dominio administrativo separado si se necesita operación visual.

## Documentación relacionada

- [Seguridad y autenticación](SECURITY_AND_AUTH.md)
- [Laragon y XAMPP](deployment/LOCAL_SUBDOMAINS.md)
- [AWS y DigitalOcean](deployment/PRODUCTION_SUBDOMAINS.md)
