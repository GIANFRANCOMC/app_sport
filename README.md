# Blapos

Plataforma multiempresa para ventas, POS, clientes, membresías, asistencias, catálogo, inventario, compras, caja, activos, biometría, restaurantes y servicios en curso.

## Arquitectura

- Laravel 10 y PHP 8.1+
- Vue 3 y Vite
- MySQL con una base landlord y una base independiente por tenant
- Acceso exclusivo mediante subdominios registrados
- `company_id` dentro de cada tenant para empresa raíz y subcompañías
- Permisos por módulo + acción y alcances de sucursal, caja y almacén

## Instalación base

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura landlord, conexión tenant y `TENANCY_BASE_DOMAIN` antes de migrar. Luego crea landlord y el tenant indicando obligatoriamente su administrador:

```bash
php artisan migrate --database=landlord --path=database/migrations/landlord
php artisan tenant:create demo --commercial-name="Demo Gym" --legal-name="Demo Gym S.A.C." --document-number=20600000001 --admin-email=admin@demo.test --admin-password="UnaClaveSegura123"
npm run dev
```

Para una instalación no tenant sobre una base vacía utiliza `php artisan system:install`. El procedimiento completo está en [Instalación de base de datos](docs/System/DATABASE_INSTALLATION.md).

En Windows, `copy .env.example .env` reemplaza al comando `cp`.

## Comandos tenant

```bash
php artisan tenant:list
php artisan tenant:health
php artisan tenant:suspend demo --force
php artisan tenant:suspend demo --activate --force
php artisan tenant:cache-clear demo
```

## Documentación

Empieza en [docs/README.md](docs/README.md). Las referencias principales son:

- [Contexto del proyecto](docs/PROJECT_CONTEXT.md)
- [Generalidades](docs/GENERALIDADES.md)
- [Arquitectura System](docs/System/ARCHITECTURE.md)
- [Multi-tenant](docs/System/MULTITENANT.md)
- [Instalación y aprovisionamiento](docs/System/DATABASE_INSTALLATION.md)
- [Pruebas automatizadas](docs/System/TESTING.md)
- [Seguridad y autenticación](docs/System/SECURITY_AND_AUTH.md)
- [Tablas System](docs/System/TABLES.md)
- [Portal Guest](docs/Guest/README.md)
- [Pendientes UI/UX](docs/UI_UX_PENDING.md)

## Desarrollo

- No ejecutes Blapos desde el dominio raíz; usa un subdominio registrado.
- No compartas `SESSION_DOMAIN` entre tenants.
- No almacenes credenciales tenant en la base landlord.
- No confíes en `company_id` enviado por frontend.
- Actualiza el módulo y `TABLES.md` cuando cambie una regla o estructura.
- Las pruebas PHP se crean o ejecutan cuando se solicitan expresamente para el flujo trabajado.
