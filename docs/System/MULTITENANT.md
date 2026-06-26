# System - Arquitectura multi-tenant por subdominio

## Resumen

Gympe funciona exclusivamente desde subdominios registrados, por ejemplo `demo.gympe.test` o `cliente.app.ejemplo.com`. El dominio raíz pertenece a otro proyecto y este código no lo atiende.

Cada tenant tiene:

- Un subdominio único de un solo nivel.
- Una base de datos MySQL propia.
- Usuarios, sesiones y datos operativos aislados.
- Un `company_id` interno que mantiene la segmentación entre empresa raíz y posibles subcompañías del mismo tenant.

No se aceptan el dominio raíz, IPs, `localhost`, dominios personalizados ni subdominios no registrados. Si el servidor web dirige por error uno de esos hosts a Gympe, la aplicación lo rechaza antes de iniciar sesión o consultar una base tenant.

## Componentes

### Landlord

La conexión `landlord` contiene únicamente el mapa operativo:

- `tenant_databases`: `slug`, `company_id`, `database_name`, estado y última resolución.
- `tenant_domains`: subdominio exacto, tenant asociado, dominio principal y estado.

El landlord no guarda host, usuario ni contraseña de MySQL. Las credenciales pertenecen al entorno seguro del servidor y nunca deben estar en Git, tablas, logs o respuestas HTTP.

### Tenant

La conexión `tenant` reutiliza host, puerto y credenciales definidos en el entorno y cambia únicamente `database`. `TenantConnectionManager` admite nombres alfanuméricos con guion bajo y exige el prefijo `TENANT_DB_PREFIX` cuando `TENANT_ENFORCE_DB_PREFIX=true`.

### Resolución HTTP

Orden real de defensas:

1. `TrustProxies` interpreta proxy y HTTPS solo desde proxies configurados.
2. `TrustHosts` acepta exclusivamente `<slug>.<TENANCY_BASE_DOMAIN>`.
3. `ResolveTenant` exige un subdominio de un nivel, no reservado, registrado y activo.
4. Se activa la base de datos tenant antes de sesión, autenticación, rutas web o API.
5. `EnsureTenantSession` verifica que la sesión pertenezca al mismo `tenant_database_id`.
6. `SecurityHeaders` incorpora encabezados de navegador y evita cachear HTML autenticado.

El resultado de resolución se cachea durante un periodo breve configurado en `TENANCY_RESOLVER_CACHE_SECONDS`. El comando de creación invalida esa clave. Suspender un tenant debe invalidar también `tenancy:resolver:<sha256-del-host>`; hasta implementar la consola landlord, se recomienda `php artisan cache:clear` tras cambios manuales de estado.

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

`SESSION_DOMAIN` debe permanecer vacío. Configurarlo como `.gympe.test` o `.ejemplo.com` compartiría cookies entre clientes y está prohibido por esta arquitectura.

## Provisionamiento

Preparar landlord:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord --force
```

Crear tenant local:

```bash
php artisan tenant:create demo \
  --commercial-name="Demo Gym" \
  --legal-name="Demo Gym S.A.C." \
  --document-number=20600000001
```

El comando crea `demo.gympe.test`, `gympe_tenant_demo`, ejecuta migraciones y habilita los datos iniciales. `--domain` solo acepta exactamente `slug + TENANCY_BASE_DOMAIN`.

En producción, la opción más segura es que infraestructura cree previamente la base y otorgue permisos al usuario de runtime. Después:

```bash
php artisan tenant:create cliente \
  --database=gympe_tenant_cliente \
  --skip-create-database \
  --commercial-name="Cliente" \
  --legal-name="Cliente S.A.C." \
  --document-number=20600000010
```

Así el usuario utilizado por PHP-FPM no requiere `CREATE DATABASE` ni `DROP DATABASE`.

## Tenants de demostración

```bash
php artisan db:seed --class=LandlordTenantDemoSeeder --force
```

| Cliente | Subdominio | Base de datos |
| --- | --- | --- |
| Demo Gym | `demo.gympe.test` | `gympe_tenant_demo` |
| Andina Fitness | `andina.gympe.test` | `gympe_tenant_andina` |
| Fit Center | `fitcenter.gympe.test` | `gympe_tenant_fitcenter` |

## Reglas obligatorias

- Nunca confiar en un `company_id` recibido del frontend; se deriva del usuario autenticado o del contexto.
- Nunca resolver un tenant solo por slug: dominio, registro y estados deben coincidir.
- Nunca guardar credenciales en `tenant_databases`.
- Nunca habilitar `SESSION_DOMAIN` compartido.
- Jobs y comandos operativos deben activar explícitamente el tenant antes de consultar modelos.
- Archivos privados deben usar una ruta o disco separado por tenant; no deben publicarse bajo una URL predecible.
- Backups, restauraciones y eliminaciones deben ejecutarse por base de datos tenant.

## Documentación relacionada

- [Seguridad y autenticación](SECURITY_AND_AUTH.md)
- [Laragon y XAMPP](deployment/LOCAL_SUBDOMAINS.md)
- [AWS y DigitalOcean](deployment/PRODUCTION_SUBDOMAINS.md)

## Pendientes

- Consola landlord separada del dominio de clientes para suspender, reactivar y auditar tenants.
- Comandos `tenant:list`, `tenant:health`, `tenant:suspend` y `tenant:cache-clear`.
- Contexto tenant obligatorio para colas, scheduler, notificaciones y almacenamiento privado.
- Rotación automatizada de credenciales y secretos mediante el proveedor cloud.
- Backups cifrados por tenant con prueba periódica de restauración.
- Auditoría de accesos landlord y alertas por intentos contra hosts desconocidos.
