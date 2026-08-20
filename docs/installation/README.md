# Instalación, desarrollo y operación de Blapos

Esta es la fuente canónica para instalar, ejecutar, validar y desplegar Blapos. Las guías de arquitectura explican el dominio; este documento concentra los comandos ejecutables y su orden.

## Contenido

1. [Modelo de ejecución](#modelo-de-ejecución)
2. [Requisitos](#requisitos)
3. [Matriz Desarrollo / Producción](#matriz-desarrollo--producción)
4. [Primera instalación](#primera-instalación)
5. [Configuración de dominios](#configuración-de-dominios)
6. [Landlord y tenants](#landlord-y-tenants)
7. [Flujo diario de desarrollo](#flujo-diario-de-desarrollo)
8. [Despliegue a producción](#despliegue-a-producción)
9. [Referencia de comandos](#referencia-de-comandos)
10. [Shell, Tinker y MySQL](#shell-tinker-y-mysql)
11. [Colas y tareas programadas](#colas-y-tareas-programadas)
12. [Validación y diagnóstico](#validación-y-diagnóstico)
13. [Operaciones destructivas](#operaciones-destructivas)

## Modelo de ejecución

Blapos es multi-tenant por subdominio y base de datos:

- `app.blapos.test` o `app.<dominio>` sirve la administración central.
- El landlord conserva el registro de tenants, dominios, administradores de plataforma, avisos y auditoría.
- Cada tenant usa su propio subdominio y una base independiente, por ejemplo `demo.blapos.test` y `blapos_tenant_demo`.
- `SESSION_DOMAIN` permanece vacío para evitar compartir sesiones entre tenants.
- El dominio raíz no debe apuntar a esta aplicación.

No ejecutar migraciones tenant sobre landlord ni migraciones landlord sobre una base tenant.

## Requisitos

| Componente | Desarrollo | Producción |
|---|---|---|
| PHP | 8.1 o superior | 8.1 o superior, PHP-FPM recomendado |
| Composer | Versión 2 | Versión 2 |
| Node.js | Versión LTS compatible con Vite 5 | Solo durante compilación |
| npm | Usar `npm ci` porque existe `package-lock.json` | Usar `npm ci` |
| MySQL | 8.x recomendado | 8.x administrado o con respaldos |
| Servidor web | Laragon Apache/Nginx | Nginx o Apache con HTTPS |
| Procesos | `schedule:work` y `queue:work` en terminales separadas | Cron y Supervisor/systemd |

Verificar el entorno PHP:

```bash
php --version
composer --version
composer check-platform-reqs
node --version
npm --version
mysql --version
```

Las extensiones habituales incluyen PDO MySQL, OpenSSL, Mbstring, XML, Ctype, Fileinfo, Tokenizer, DOM, GD y Zip.

## Matriz Desarrollo / Producción

| Operación | Desarrollo | Producción |
|---|---|---|
| Clonar | `git clone <URL> blapos` | `git clone --branch main --single-branch <URL> blapos` |
| PHP | `composer install` | `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction` |
| JavaScript | `npm ci` | `npm ci` durante el build |
| Variables | Copiar `.env.example` y ajustar valores locales | Crear `.env` desde el gestor de secretos; nunca versionarlo |
| Clave Laravel | `php artisan key:generate` una sola vez | `php artisan key:generate --force` solo en la primera instalación |
| Assets | `npm run dev` | `npm run build` |
| Landlord inicial | `php artisan platform:install` | `php artisan platform:install --no-interaction` con credenciales seguras en el entorno |
| Tenant inicial | `php artisan tenant:create <slug>` | Preferir el panel central o `tenant:create` de forma interactiva |
| Migraciones landlord | `php artisan migrate --database=landlord --path=database/migrations/landlord` | Añadir `--force` |
| Migraciones tenant | Conectar una base tenant concreta y ejecutar `migrate --database=tenant` | Ejecutar por cada base tenant y añadir `--force` |
| Cachés | `php artisan optimize:clear` | `php artisan optimize` después de migrar |
| Cola | `php artisan queue:work --tries=3` | Supervisor/systemd con `queue:work --sleep=3 --tries=3 --timeout=120` |
| Scheduler | `php artisan schedule:work` | Cron: `php artisan schedule:run` cada minuto |
| Logs | `storage/logs/laravel.log` | Canal centralizado y rotación |
| Debug | `APP_DEBUG=true` solo local | `APP_DEBUG=false` |
| HTTPS | Opcional local | Obligatorio; `SESSION_SECURE_COOKIE=true` |
| Pruebas | `php artisan test` | Ejecutarlas en CI antes del despliegue, nunca sobre datos productivos |

## Primera instalación

### 1. Clonar

Linux/macOS:

```bash
git clone <URL_DEL_REPOSITORIO> blapos
cd blapos
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Windows PowerShell:

```powershell
git clone <URL_DEL_REPOSITORIO> blapos
Set-Location blapos
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

No usar `composer update` ni `npm update` como parte de una instalación normal. Esos comandos cambian versiones y deben ejecutarse como una tarea de actualización controlada.

### 2. Configurar `.env`

Valores locales mínimos:

```dotenv
APP_NAME=Blapos
APP_ENV=local
APP_DEBUG=true
APP_URL=http://demo.blapos.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blapos
DB_USERNAME=root
DB_PASSWORD=

LANDLORD_DB_CONNECTION=landlord
LANDLORD_DB_DATABASE=blapos_landlord
TENANT_DB_CONNECTION=tenant
TENANT_DB_PREFIX=blapos_tenant_
TENANT_ENFORCE_DB_PREFIX=true

TENANCY_BASE_DOMAIN=blapos.test
TENANCY_PLATFORM_SUBDOMAIN=app
TENANCY_ENFORCE_SUBDOMAINS=true
SESSION_DOMAIN=
```

En producción:

- usar contraseñas distintas y secretas para MySQL;
- establecer `APP_ENV=production` y `APP_DEBUG=false`;
- usar URLs HTTPS;
- configurar `SESSION_SECURE_COOKIE=true`;
- definir `TRUSTED_PROXIES` si existe balanceador o proxy;
- no conservar `PLATFORM_ADMIN_PASSWORD=Admin12345!`;
- no guardar secretos en comandos, documentación, Git ni logs.

### 3. Crear la base landlord

```sql
CREATE DATABASE blapos_landlord
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Puede ejecutarse desde el cliente MySQL:

```bash
mysql -u root -p
```

### 4. Instalar la plataforma central

Desarrollo:

```bash
php artisan platform:install
```

Producción no interactiva:

```bash
php artisan platform:install --no-interaction
```

`platform:install` aplica exclusivamente las migraciones landlord y crea o actualiza el administrador central usando `PLATFORM_ADMIN_NAME`, `PLATFORM_ADMIN_EMAIL` y `PLATFORM_ADMIN_PASSWORD`.

Para crear o rotar un administrador sin exponer la contraseña en el historial:

```bash
php artisan platform:admin admin@app.blapos.test
```

El comando solicitará la contraseña de forma oculta.

### 5. Crear el primer tenant

```bash
php artisan tenant:create demo \
  --commercial-name="Empresa demo" \
  --legal-name="EMPRESA DEMO S.A.C." \
  --document-number=20600000001 \
  --admin-name="Administrador" \
  --admin-email=admin@demo.test
```

En modo interactivo, la contraseña se solicita de forma segura. El comando:

1. crea `blapos_tenant_demo` si el usuario MySQL tiene permiso;
2. registra tenant y dominio en landlord;
3. conecta la base tenant;
4. ejecuta migraciones y el catálogo inicial;
5. aprovisiona empresa, permisos, sede, almacén, caja y administrador.

Alternativamente, crear el cliente desde `app.blapos.test`; no ejecutar ambos flujos para la misma alta.

## Configuración de dominios

### Windows con Laragon

El proyecto debe vivir en:

```text
C:\laragon\www\blapos
```

Entradas locales mínimas en `hosts`, editado como administrador:

```text
127.0.0.1 blapos.test
127.0.0.1 app.blapos.test
127.0.0.1 demo.blapos.test
```

El VirtualHost debe apuntar a `C:/laragon/www/blapos/public` y aceptar `*.blapos.test`.

### Linux/Nginx

El DNS wildcard `*.app.ejemplo.com` debe apuntar al servidor de Blapos. El certificado debe cubrir ese mismo nivel. Consultar [producción por subdominios](../System/deployment/PRODUCTION_SUBDOMAINS.md).

## Landlord y tenants

### Migrar landlord

Desarrollo:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord
```

Producción:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord --force
```

### Migrar un tenant existente

No existe actualmente un comando `tenant:migrate-all`. Cada base debe seleccionarse explícitamente.

Antes de usar variables temporales para seleccionar una base, ejecutar `php artisan optimize:clear`; una configuración cacheada ignora cambios posteriores del entorno durante ese proceso.

Linux/bash:

```bash
DB_CONNECTION=tenant \
TENANT_DB_DATABASE=blapos_tenant_demo \
php artisan migrate --database=tenant --path=database/migrations --force
```

Windows PowerShell:

```powershell
$env:DB_CONNECTION = "tenant"
$env:TENANT_DB_DATABASE = "blapos_tenant_demo"
php artisan migrate --database=tenant --path=database/migrations --force
Remove-Item Env:DB_CONNECTION
Remove-Item Env:TENANT_DB_DATABASE
```

En producción, la infraestructura debe iterar un inventario confiable de bases tenant. No construir comandos SQL a partir de texto libre ni analizar la salida visual de `tenant:list`.

### Instalar una base no tenant

Este flujo es exclusivo para instalaciones de una sola base y no sustituye landlord + tenants:

```bash
php artisan system:install \
  --slug=mi-empresa \
  --commercial-name="Mi empresa" \
  --legal-name="MI EMPRESA S.A.C." \
  --document-number=20123456789 \
  --admin-name="Administrador" \
  --admin-email=admin@miempresa.com
```

La contraseña se solicita en modo interactivo. No usar `system:install --fresh` sobre información que deba conservarse.

## Flujo diario de desarrollo

Terminal 1, frontend:

```bash
npm run dev
```

Terminal 2, cola:

```bash
php artisan queue:work --sleep=1 --tries=3 --timeout=120
```

Terminal 3, scheduler:

```bash
php artisan schedule:work
```

Después de cambiar rutas, configuración o `.env`:

```bash
php artisan optimize:clear
```

Después de cambiar clases o namespaces:

```bash
composer dump-autoload
```

No se recomienda `php artisan serve` para validar tenancy por subdominios. Usar el VirtualHost local para conservar el `Host` real.

## Despliegue a producción

### Primera instalación

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan storage:link
php artisan platform:install --no-interaction
php artisan optimize
```

Después se crea cada tenant desde la plataforma central o mediante `tenant:create`.

### Despliegue posterior

```bash
php artisan down --retry=60
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --database=landlord --path=database/migrations/landlord --force
# Migrar aquí cada base tenant de forma explícita.
php artisan optimize
php artisan queue:restart
php artisan up
```

Si cualquier migración o validación falla, no ejecutar `artisan up` hasta revisar el estado. La reversión de código no garantiza la reversión de una migración; debe existir respaldo previo.

Permisos habituales en Linux:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

## Referencia de comandos

### Composer y frontend

| Comando | Uso |
|---|---|
| `composer install` | Instala exactamente `composer.lock`. |
| `composer install --no-dev --optimize-autoloader` | Dependencias productivas y autoload optimizado. |
| `composer dump-autoload` | Regenera autoload después de mover clases. |
| `composer validate --no-check-publish` | Valida `composer.json` y `composer.lock`. |
| `composer format:php` | Aplica comillas, Pint y estructuras del estándar Blapos. |
| `composer format:php-check` | Comprueba el formato sin modificar archivos. |
| `composer check:php-syntax` | Comprueba sintaxis de todos los PHP del proyecto. |
| `npm ci` | Instala exactamente `package-lock.json`. |
| `npm run dev` | Inicia Vite con recarga en desarrollo. |
| `npm run build` | Genera assets productivos en `public/build`. |
| `npm run build:css:system` | Reconstruye el CSS consolidado de System. |

### Plataforma central

| Comando | Uso | Precaución |
|---|---|---|
| `php artisan platform:install` | Migra landlord y crea/actualiza el administrador inicial. | En producción exige credencial segura. |
| `php artisan platform:admin <email>` | Crea, reactiva o rota un administrador central. | Omitir `--password` para entrada oculta. |
| `php artisan platform:admin <email> --inactive` | Crea o actualiza el acceso como inactivo. | Puede impedir el acceso de esa cuenta. |

### Tenants

| Comando | Uso | Precaución |
|---|---|---|
| `php artisan tenant:list` | Lista tenant, dominio, base y estado desde landlord. | No abre las bases tenant. |
| `php artisan tenant:list --status=active` | Filtra por estado. | Estados: `active`, `inactive`, `suspended`, `provisioning`. |
| `php artisan tenant:health [slug]` | Comprueba conexión, esquema base, empresa y latencia. | Sin slug revisa todos. |
| `php artisan tenant:cache-clear [slug]` | Invalida caché de resolución de uno o todos. | No equivale a `cache:clear`. |
| `php artisan tenant:suspend <slug>` | Suspende tenant y dominio. | Pide confirmación. |
| `php artisan tenant:suspend <slug> --force` | Suspende sin confirmación. | Reservado para automatización controlada. |
| `php artisan tenant:suspend <slug> --activate` | Reactiva el tenant. | Verificar salud después. |
| `php artisan tenant:create <slug>` | Crea base, registro, migraciones y datos iniciales. | No repetir sin comprender `--force`. |

Opciones avanzadas de `tenant:create`:

| Opción | Significado |
|---|---|
| `--domain=` | Dominio completo, coherente con slug y dominio base. |
| `--database=` | Base explícita; debe respetar `TENANT_DB_PREFIX`. |
| `--company-id=` | ID de empresa raíz, por defecto `1`. |
| `--force` | Permite actualizar un registro landlord existente. |
| `--skip-create-database` | Usa una base ya creada por infraestructura. |
| `--skip-migrate` | Registra base y dominio sin migrar ni aprovisionar. |
| `--skip-cache-clear` | Omite `optimize:clear` al finalizar. |

### Esquema y organizaciones

| Comando | Uso | Precaución |
|---|---|---|
| `php artisan system:install` | Instala esquema y organización en una conexión no tenant. | No ejecutar sobre landlord. |
| `php artisan system:install --fresh` | Reconstruye todas las tablas. | Elimina datos. |
| `php artisan system:sync [--company=ID]` | Proyecta menú y permisos de acceso total. | Ejecutar dentro de la conexión empresarial correcta. |
| `php artisan system:doctor [--company=ID]` | Diagnostica tablas, catálogo y referencias esenciales. | Recomendado después de migrar. |
| `php artisan company:enable <ID>` | Completa defaults de una empresa idempotentemente. | Activa módulos y permisos base. |
| `php artisan company:enable <ID> --skip-modules` | Completa defaults sin modificar módulos. | Útil para reparaciones acotadas. |

### Operación multi-tenant programada

| Comando | Uso |
|---|---|
| `php artisan notifications:send-subscriptions --limit=100` | Envía notificaciones de membresías pendientes. |
| `php artisan subscriptions:cancel-expired --limit=1000` | Inactiva membresías vencidas. |
| `php artisan attendances:close-stale-customers --limit=500` | Cierra asistencias abiertas fuera del límite. |
| `php artisan attendances:prune-customers --dry-run` | Cuenta asistencias antiguas elegibles sin borrar. |
| `php artisan attendances:prune-customers --months=12 --limit=1000` | Depura asistencias según retención. |

Los cuatro comandos aceptan `--tenant=<slug>` y/o `--company=<id>` cuando su firma lo indica. Probar primero con un tenant concreto.

### Laravel operativo

| Comando | Uso |
|---|---|
| `php artisan about` | Resume entorno y configuración activa. |
| `php artisan list` | Lista comandos disponibles. |
| `php artisan help <comando>` | Muestra argumentos y opciones reales. |
| `php artisan route:list` | Lista rutas. Puede filtrarse con `--path` o `--name`. |
| `php artisan migrate:status --database=<conexión>` | Revisa migraciones sin modificarlas. |
| `php artisan optimize:clear` | Limpia cachés de configuración, rutas, eventos y vistas. |
| `php artisan optimize` | Genera cachés optimizadas para producción. |
| `php artisan config:cache` | Cachea configuración. |
| `php artisan route:cache` | Cachea rutas. |
| `php artisan view:cache` | Compila vistas Blade. |
| `php artisan queue:work` | Procesa trabajos en cola. |
| `php artisan queue:restart` | Solicita reinicio elegante de workers. |
| `php artisan schedule:list` | Muestra tareas programadas. |
| `php artisan schedule:run` | Ejecuta las tareas vencidas una vez. |
| `php artisan schedule:work` | Mantiene el scheduler activo en desarrollo. |
| `php artisan storage:link` | Crea el enlace público de almacenamiento. |
| `php artisan down` / `up` | Activa o desactiva mantenimiento. |
| `php artisan test` | Ejecuta toda la suite. |
| `php artisan test --testsuite=Unit` | Ejecuta pruebas unitarias/arquitectónicas. |

## Shell, Tinker y MySQL

### Shell del sistema

Ejecutar comandos desde la raíz del repositorio. Confirmar antes:

```bash
pwd
php artisan about
```

En PowerShell:

```powershell
Get-Location
php artisan about
```

### Laravel Tinker

Abrir:

```bash
php artisan tinker
```

Consultar landlord sin mostrar secretos:

```php
App\Models\System\Tenancy\TenantDatabase::query()
    ->select(["public_id", "slug", "database_name", "status"])
    ->get();
```

Conectar un tenant durante una sesión Tinker:

```php
$tenant = App\Models\System\Tenancy\TenantDatabase::query()
    ->where("slug", "demo")
    ->firstOrFail();

app(App\Services\System\Tenancy\TenantConnectionManager::class)
    ->connect($tenant);

Illuminate\Support\Facades\DB::connection("tenant")
    ->getDatabaseName();
```

Salir con `exit`. No ejecutar escrituras manuales en producción si existe un servicio o comando de dominio equivalente.

### MySQL

```bash
mysql -u <usuario> -p
```

Comandos de inspección:

```sql
SHOW DATABASES;
USE blapos_landlord;
SHOW TABLES;
SELECT slug, database_name, status FROM tenant_databases ORDER BY slug;
```

No guardar contraseñas en la línea de comandos ni usar `DROP DATABASE` sin respaldo y validación explícita del nombre.

## Colas y tareas programadas

El scheduler registrado ejecuta:

| Frecuencia | Tarea |
|---|---|
| Cada 5 minutos | `notifications:send-subscriptions --limit=100` |
| Cada hora | `subscriptions:cancel-expired` |
| Cada hora | `attendances:close-stale-customers --limit=500` |
| Diariamente 03:20 | `attendances:prune-customers --limit=1000` |

Cron productivo:

```cron
* * * * * cd /ruta/al/blapos && php artisan schedule:run >> /dev/null 2>&1
```

Ejemplo conceptual de Supervisor:

```ini
[program:blapos-worker]
command=php /ruta/al/blapos/artisan queue:work --sleep=3 --tries=3 --timeout=120
directory=/ruta/al/blapos
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
```

Después de desplegar código:

```bash
php artisan queue:restart
```

## Validación y diagnóstico

Validación mínima antes de entregar o desplegar:

```bash
composer validate --no-check-publish
composer format:php-check
composer check:php-syntax
php artisan test
npm run build
php artisan route:list
php artisan schedule:list
```

Después de aprovisionar:

```bash
php artisan tenant:list
php artisan tenant:health
```

Dentro de una conexión empresarial:

```bash
php artisan system:doctor
```

Base de pruebas:

- `phpunit.xml` usa `blapos_testing`.
- Nunca apuntar PHPUnit a landlord o a un tenant real.
- No ejecutar dos suites simultáneas contra la misma base de pruebas porque ambas reconstruyen el esquema.

## Operaciones destructivas

Requieren respaldo, objetivo exacto y confirmación:

- `php artisan migrate:fresh`;
- `php artisan system:install --fresh`;
- `php artisan db:wipe`;
- `DROP DATABASE`;
- eliminación de `storage` o archivos cargados;
- rollback de migraciones con datos reales.

`migrate:rollback` revierte código de migración, pero no garantiza recuperar información eliminada. En producción, preferir migraciones hacia adelante y restauración desde respaldos probados.

## Documentación relacionada

- [Arquitectura multi-tenant](../System/MULTITENANT.md)
- [Instalación interna de base de datos](../System/DATABASE_INSTALLATION.md)
- [Subdominios locales](../System/deployment/LOCAL_SUBDOMAINS.md)
- [Producción](../System/deployment/PRODUCTION_SUBDOMAINS.md)
- [Pruebas](../System/TESTING.md)
- [Seguridad](../System/SECURITY_AND_AUTH.md)
