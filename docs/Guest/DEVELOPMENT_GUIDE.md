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

- Crear FormRequests para reclamaciones y asistencia publica.
- Definir politicas de rate limiting.
- Documentar payloads publicos.
- Probar rutas con empresa inexistente, sucursal invalida y datos incompletos.

