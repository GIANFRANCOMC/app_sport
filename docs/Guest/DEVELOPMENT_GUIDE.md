# Guest - Guia de desarrollo

## Principios

- Guest es publico: validar todo.
- No exponer datos internos de System.
- Mantener la empresa derivada de `company_slug`.
- Reutilizar componentes Guest, no System, salvo que sea intencional.
- Mantener formularios publicos simples y robustos.

## Al crear una funcionalidad Guest

Revisar:

- Ruta en `routes/Guest`.
- Middleware `company.exists`.
- Controlador Guest.
- Modelo Guest o servicio reutilizado.
- Vista Blade Guest.
- Pagina Vue Guest.
- Validacion server-side.
- Rate limiting si el endpoint puede abusarse.
- Documentacion en `Guest/modules`.

## Buenas practicas

- Responder con mensajes claros para clientes finales.
- No mostrar ids internos si no es necesario.
- Usar tokens firmados para acciones publicas sensibles.
- Registrar IP/user agent en formularios publicos importantes.
- Evitar que errores internos lleguen tal cual al visitante.

## Mejoras recomendadas

- Mantener FormRequests públicos por recurso y ampliar sus reglas cuando cambie el contrato.
- Mantener rate limiting diferenciado por empresa, IP o clave de dispositivo.
- Documentar cada payload público junto con los datos que deliberadamente no expone.
- Probar rutas con empresa inexistente, sucursal invalida y datos incompletos.

## Pendientes y mejoras por realizar

- Reforzar criterios de seguridad publica, accesibilidad y mensajes para visitantes.
- Evitar reutilizar componentes internos de System sin revisar exposicion de datos y dependencias visuales.
