# Desarrollo local por subdominios

## Regla

El dominio raíz no apunta a este proyecto. Para Gympe se usa exclusivamente `*.gympe.test`; cada tenant registrado necesita un host como `demo.gympe.test`.

Windows `hosts` no admite comodines. Añadir cada tenant en `C:\Windows\System32\drivers\etc\hosts` como administrador:

```text
127.0.0.1 demo.gympe.test
127.0.0.1 andina.gympe.test
127.0.0.1 fitcenter.gympe.test
127.0.0.1 app.gympe.test
```

## Laragon con Apache

Crear un virtual host dedicado a subdominios. El dominio raíz debe tener otro virtual host y otro `DocumentRoot`.

```apache
<VirtualHost *:80>
    ServerName tenant-router.gympe.test
    ServerAlias *.gympe.test
    DocumentRoot "C:/laragon/www/gympe/public"

    <Directory "C:/laragon/www/gympe/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Reiniciar Apache desde Laragon. Configurar `.env`:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://demo.gympe.test
TENANCY_BASE_DOMAIN=gympe.test
TENANCY_PLATFORM_SUBDOMAIN=app
TENANCY_ENFORCE_SUBDOMAINS=true
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
```

## Laragon con Nginx

```nginx
server {
    listen 80;
    server_name ~^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.gympe\.test$;
    root C:/laragon/www/gympe/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

El socket o puerto PHP debe coincidir con Laragon.

## XAMPP

Habilitar `mod_rewrite` y la inclusión de `conf/extra/httpd-vhosts.conf`. Agregar:

```apache
<VirtualHost *:80>
    ServerName tenant-router.gympe.test
    ServerAlias *.gympe.test
    DocumentRoot "C:/xampp/htdocs/gympe/public"

    <Directory "C:/xampp/htdocs/gympe/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Reiniciar Apache y crear las entradas `hosts` indicadas arriba.

## Preparación

```bash
php artisan platform:install
php artisan tenant:create demo --commercial-name="Demo Gym" --legal-name="Demo Gym S.A.C." --document-number=20600000001 --admin-email=admin@demo.test --admin-password="UnaClaveSegura123"
php artisan optimize:clear
```

Después del alta, ejecutar `php artisan system:doctor` sobre la conexión tenant activa cuando se necesite verificar catálogo y referencias. Consulta [Instalación y aprovisionamiento](../DATABASE_INSTALLATION.md).

Abrir `http://app.gympe.test` para la administración SaaS y `http://demo.gympe.test` para el tenant. `http://gympe.test`, `http://localhost` y subdominios no registrados deben ser rechazados por este código.

Credenciales locales iniciales: `admin@app.gympe.test` / `Admin12345!`. Cambiarlas con `php artisan platform:admin admin@app.gympe.test` cuando la base deje de ser descartable.

## Diagnóstico

- `400`: el host no cumple el patrón estricto de subdominio.
- `404`: el formato es válido, pero el subdominio no está registrado y activo.
- `500`: revisar conexión landlord, migraciones y nombre de la base tenant.
- Cookie compartida: confirmar que `SESSION_DOMAIN` esté vacío y eliminar cookies antiguas del navegador.
