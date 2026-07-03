# 01 - Seguridad publica

## Problema

Guest esta expuesto a visitantes. Cualquier formulario o endpoint puede ser abusado.

## Requerimientos sugeridos

- Rate limiting por IP y empresa.
- Captcha para reclamaciones si hay spam.
- Tokens firmados para links de asistencia.
- Tokens por dispositivo para biometricos.
- Respuestas publicas sin detalles internos.

## Pendientes y mejoras por realizar

## Estado backend implementado

- Rate limiting diferenciado por empresa, IP y recurso para reclamos, consulta de estado y asistencia pública.
- FormRequest públicos, honeypot, límite diario de reclamos y respuestas sin datos internos.
- Credenciales por dispositivo, firma HMAC, idempotencia y bitácora de eventos biométricos.
- La incorporación visual de captcha y enlaces firmados se mantiene únicamente en `docs/UI_UX_PENDING.md`.

- Definir limites de rate limiting por recurso publico.
- Documentar validaciones anti abuso por formulario y por empresa.
