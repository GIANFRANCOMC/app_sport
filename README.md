# Blapos

Plataforma multiempresa para ventas, POS, clientes, membresías, asistencias, catálogo, inventario, compras, caja, activos, biometría, restaurantes y servicios en curso.

## Arquitectura

- Laravel 10 y PHP 8.1+
- Vue 3 y Vite
- MySQL con una base landlord y una base independiente por tenant
- Acceso exclusivo mediante subdominios registrados
- `company_id` dentro de cada tenant para empresa raíz y subcompañías
- Permisos por módulo + acción y alcances de sucursal, caja y almacén

## Instalación y operación

La fuente canónica para clonar, configurar, desarrollar, desplegar y operar landlord y tenants es la [guía unificada de instalación](docs/1-Instalation.md). Incluye la matriz Desarrollo/Producción, comandos Artisan propios, shell, Tinker, MySQL, colas, scheduler, pruebas y operaciones destructivas.

## Documentación

Empieza en [docs/README.md](docs/README.md). Las referencias principales son:

- [Contexto del proyecto](docs/PROJECT_CONTEXT.md)
- [Generalidades](docs/GENERALIDADES.md)
- [Arquitectura System](docs/System/ARCHITECTURE.md)
- [Multi-tenant](docs/System/MULTITENANT.md)
- [Instalación y aprovisionamiento](docs/System/DATABASE_INSTALLATION.md)
- [Instalación y comandos unificados](docs/1-Instalation.md)
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
