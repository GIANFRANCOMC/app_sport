# System - Seguridad y autenticación

## Dónde vive cada dato

- Las cuentas están en `users` dentro de la base de datos del tenant.
- La contraseña se almacena mediante hash; nunca se recupera en texto plano.
- El guard `web` usa el proveedor Eloquent `App\Models\System\Organizations\User` sobre la conexión tenant activa.
- El navegador conserva solo cookies host-only. El contenido de sesión vive en el driver configurado por `SESSION_DRIVER`.
- En producción se recomienda Redis para sesión y caché. El driver `cookie` no se recomienda porque aumenta datos enviados por petición.
- El token CSRF está ligado a la sesión y Laravel lo valida en las rutas web mutables.

## Inicio de sesión

1. El host se valida y el tenant se conecta antes de abrir la sesión.
2. Se consulta usuario y compañía únicamente dentro de la BD tenant.
3. El rate limit combina tenant, email normalizado e IP. Un cliente no bloquea accidentalmente el login de otro.
4. Tras autenticar se regenera el ID de sesión para impedir session fixation.
5. La sesión guarda `_tenant_database_id`. Un cambio de tenant invalida la sesión y regenera CSRF.

Al cerrar sesión se ejecutan logout, invalidación completa y regeneración del token.

## Cookies recomendadas

```env
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
```

`HttpOnly` ya está activo en `config/session.php`. `Secure` es obligatorio en producción. `SameSite=lax` conserva navegación normal y protege el flujo actual; cambiar a `strict` exige validar enlaces externos y futuros proveedores de identidad.

Cada cookie de sesión usa `TENANT_SESSION_COOKIE_PREFIX + slug + hash`, pero la defensa principal es que sea host-only. El hash del nombre no es una clave secreta.

## Encabezados

`SecurityHeaders` aplica:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` permite cámara solo al mismo origen y desactiva micrófono, geolocalización y pagos por defecto
- CSP mínima para bloquear objetos, cambios de base URL y framing externo
- HSTS en HTTPS y producción
- `no-store` para HTML autenticado

La CSP es deliberadamente mínima para no romper scripts heredados. Debe endurecerse después de inventariar scripts inline, fuentes, imágenes y conexiones externas.

## Secretos

- `.env` no se versiona.
- `APP_KEY`, contraseñas DB, SMTP, tokens cloud y CAPTCHA no se guardan en tablas ni documentación.
- `APP_DEBUG=false` y `LOG_LEVEL=warning` en producción.
- El registry landlord solo guarda nombres de BD, nunca credenciales.
- En AWS usar Secrets Manager o variables inyectadas por el servicio; en DigitalOcean usar secretos cifrados del servicio o variables protegidas.
- Rotar un secreto requiere actualizar el entorno de todos los nodos y reiniciar workers.

## Proxy, CORS y red

- `TRUSTED_PROXIES` debe contener solo IPs/CIDR del proxy. Usar `*` únicamente si el servidor de origen no acepta tráfico directo desde Internet.
- CORS parte sin orígenes permitidos. Añadir exclusivamente orígenes necesarios mediante `CORS_ALLOWED_ORIGINS` o patrones controlados.
- MySQL no debe estar expuesto públicamente; solo los servidores de aplicación y procesos de administración pueden conectarse.
- El usuario runtime no debe crear ni eliminar bases. Usar `tenant:create --skip-create-database` en producción.

## Lista de salida a producción

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS y certificado wildcard vigente.
- `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=` y Redis protegido.
- Dominio raíz dirigido al proyecto independiente; wildcard dirigido a Gympe.
- Servidor de origen inaccesible directamente cuando exista balanceador.
- Backups cifrados, restauración probada y retención definida.
- Dependencias auditadas, logs sin datos sensibles y alertas de autenticación.
- Workers reiniciados después de despliegue o rotación de secretos.

## Pendientes

- Segundo factor para administradores y operaciones críticas.
- Revocación de todas las sesiones de un usuario al cambiar contraseña o estado.
- Historial de inicio/cierre de sesión, IP, dispositivo y tenant.
- CSP estricta con nonce para eliminar `unsafe-inline` cuando se modernicen vistas heredadas.
- Gestión de secretos con rotación automática.
