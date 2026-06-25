# System - Arquitectura Multi-tenant

## Objetivo

Gympe separa los datos de cada cliente en una base de datos propia. El registro central vive en una conexión `landlord`; cada cliente se resuelve por subdominio o dominio personalizado y se conecta dinámicamente a una conexión `tenant`.

Esta separación no elimina `company_id`. Dentro de cada base tenant, `company_id` sigue siendo obligatorio para reforzar filtros, permisos, sucursales y posibles subcompañías internas del mismo cliente. En producción esto permite que el cliente perciba su espacio como independiente y que el sistema conserve una defensa adicional por empresa.

## Conexiones

- `mysql`: conexión tradicional de compatibilidad local. Se mantiene como fallback cuando no se resuelve tenant.
- `landlord`: base central que guarda el mapa de tenants, dominios y base de datos asignada.
- `tenant`: conexión dinámica. Se configura en runtime con la base de datos del cliente resuelto.

Variables principales:

```env
LANDLORD_DB_CONNECTION=landlord
LANDLORD_DB_HOST=127.0.0.1
LANDLORD_DB_PORT=3306
LANDLORD_DB_DATABASE=gympe_landlord
LANDLORD_DB_USERNAME=root
LANDLORD_DB_PASSWORD=

TENANT_DB_CONNECTION=tenant
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=root
TENANT_DB_PASSWORD=
TENANT_DB_PREFIX=gympe_tenant_
TENANT_SESSION_COOKIE_PREFIX=gympe_tenant
TENANCY_BASE_DOMAIN=gympe.test
TENANCY_CENTRAL_DOMAINS=localhost,127.0.0.1,gympe.test

SESSION_ENCRYPT=true
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false
```

## Tablas landlord

### tenant_databases

Registro central de cada tenant. Campos relevantes:

- `slug`: identificador único para subdominio y uso operativo.
- `company_id`: ID raíz de `companies` dentro de la BD tenant. Es una referencia lógica, no una FK, porque la tabla `companies` vive en otra base de datos.
- `connection_name`: normalmente `tenant`.
- `database_name`: base de datos física del cliente.
- `db_driver`, `db_host`, `db_port`, `db_username`, `db_password`: credenciales de conexión. La contraseña se guarda cifrada.
- `status`: `provisioning`, `active`, `inactive`, `suspended`.
- `last_resolved_at`: última vez que un request resolvió este tenant.

### tenant_domains

Dominios que apuntan a un tenant.

- `tenant_database_id`: tenant asociado.
- `domain`: dominio completo y único, por ejemplo `demo.gympe.test` o `app.cliente.com`.
- `type`: `subdomain` o `custom`.
- `is_primary`: dominio principal.
- `status`: `active` o `inactive`.

## Resolución por request

El middleware `ResolveTenant` corre al inicio del grupo `web`.

1. Lee el host del request.
2. Si el host está en `TENANCY_CENTRAL_DOMAINS`, no cambia la conexión y la app usa el fallback normal.
3. Busca coincidencia exacta en `tenant_domains.domain`.
4. Si no existe, intenta resolver el primer segmento como subdominio de `TENANCY_BASE_DOMAIN`.
5. Si encuentra tenant activo, configura `database.connections.tenant`, purga la conexión anterior y establece `tenant` como conexión por defecto.
6. Guarda el tenant activo en `TenantContext` para que otros servicios puedan consultarlo durante la misma petición.

## Provisionamiento por comando

El comando principal es:

```bash
php artisan tenant:create demo --commercial-name="Demo Gym" --legal-name="Demo Gym S.A.C." --document-number=20600000000
```

Esto crea:

1. Registro central en landlord.
2. Dominio `demo.gympe.test` si no se pasa `--domain`.
3. Base de datos `gympe_tenant_demo` si no se pasa `--database`.
4. Migraciones tenant.
5. Datos iniciales de empresa mediante `company:enable`.
6. Limpieza de caché al finalizar, salvo que se use `--skip-cache-clear`.

Ejemplo con dominio personalizado:

```bash
php artisan tenant:create demo --domain=app.cliente.com --database=gympe_tenant_cliente
```

Opciones útiles:

- `--company-id=1`: ID raíz dentro de la BD tenant.
- `--force`: actualiza registro landlord existente.
- `--skip-migrate`: crea BD y registro, pero no migra.
- `--skip-cache-clear`: evita limpiar cachés al final.

La migración landlord también puede ejecutarse manualmente:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord
```

El comando `tenant:create` llama internamente a `LandlordSchemaService::ensure()`, por lo que puede crear las tablas landlord si aún no existen.

## Tenants demo

Para pruebas internas se dejan tres tenants base:

| Cliente | Dominio local | Base de datos |
| --- | --- | --- |
| Demo Gym | `demo.gympe.test` | `gympe_tenant_demo` |
| Andina Fitness | `andina.gympe.test` | `gympe_tenant_andina` |
| Fit Center | `fitcenter.gympe.test` | `gympe_tenant_fitcenter` |

Se pueden crear o rehidratar con:

```bash
php artisan db:seed --class=LandlordTenantDemoSeeder --force
```

El seeder usa `tenant:create --force` para que sea repetible durante pruebas.

## Comandos operativos

Ejecutar registry landlord:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord --force
```

Crear tenant individual:

```bash
php artisan tenant:create demo --commercial-name="Demo Gym" --legal-name="Demo Gym S.A.C." --document-number=20600000001 --force
```

Crear tenant con dominio personalizado:

```bash
php artisan tenant:create cliente --domain=app.cliente.com --database=gympe_tenant_cliente --commercial-name="Cliente" --legal-name="Cliente S.A.C." --document-number=20600000010
```

Limpiar cachés después de cambios de infraestructura:

```bash
php artisan optimize:clear
```

## Seguridad de sesión por tenant

La capa tenant incluye dos defensas para reducir riesgo de robo o reutilización de sesiones entre clientes:

1. `ResolveTenant` define un nombre de cookie de sesión distinto por tenant antes de que Laravel inicie la sesión. El nombre usa `TENANT_SESSION_COOKIE_PREFIX`, el slug y un hash corto.
2. `EnsureTenantSession` guarda el `tenant_database_id` dentro de la sesión. Si una sesión intenta operar contra otro tenant, invalida la sesión y regenera el token CSRF.

Además, `SESSION_ENCRYPT=true` cifra el contenido de sesión por defecto y `SESSION_SAME_SITE=lax` mantiene protección razonable contra CSRF sin romper navegación normal.

Para producción se recomienda:

- Usar HTTPS y `SESSION_SECURE_COOKIE=true`.
- Mantener `SESSION_DOMAIN` vacío salvo decisión explícita. Así la cookie queda host-only por dominio/subdominio.
- No compartir cookies entre subdominios de clientes.
- Registrar sólo dominios verificados en `tenant_domains`.
- Revisar `TENANCY_CENTRAL_DOMAINS` para que sólo incluya dominios administrativos reales.

## Acceso local y producción

Para local con Laragon se recomienda usar `TENANCY_BASE_DOMAIN=gympe.test`. Si el entorno no resuelve comodines, agregar cada subdominio al archivo hosts o configurar DNS local.

Para producción:

- Configurar DNS wildcard, por ejemplo `*.gympe.com` apuntando al servidor.
- Para dominios personalizados, crear un CNAME o A record desde el dominio del cliente hacia la infraestructura de Gympe.
- Registrar cada dominio personalizado en `tenant_domains`.
- Marcar un solo dominio principal por tenant cuando exista más de uno.

## Company ID dentro del tenant

Cada BD tenant puede tener una empresa raíz y subcompañías internas. Por eso las tablas operativas siguen usando `company_id` y FK a `companies` dentro de la misma BD tenant.

Regla de implementación:

- No remover `company_id` de tablas existentes.
- Toda tabla tenant con `company_id` debe declarar FK a `companies` cuando la relación sea local a la BD tenant.
- `tenant_databases.company_id` es la excepción: apunta al ID raíz dentro de otra base y por eso no puede tener FK real.
- Servicios y FormRequests no deben confiar en `company_id` del frontend.

## Caché

La caché funcional sigue acotada por empresa: `company_id`, rol, página o módulo. Como cada cliente tiene su propia BD, el riesgo de mezcla baja, pero no se elimina el scoping interno.

Después de cambios de dominios, tenants o configuración sensible:

```bash
php artisan optimize:clear
```

## Pendientes recomendados

- Crear pantalla interna de administración landlord para dominios, estado de tenant y credenciales, con permisos muy restringidos.
- Agregar health-check por tenant para validar conexión, migraciones pendientes y tamaño de base.
- Definir proceso de suspensión de tenant sin borrar datos.
- Automatizar certificado SSL para dominios personalizados.
- Revisar workers/queues para que cada job guarde `tenant_database_id` y reactive la conexión antes de ejecutar.
- Crear comando `tenant:list` y `tenant:health` cuando se empiece a operar más de un cliente real.
