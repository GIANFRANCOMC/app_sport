# 00 - Alcance publico Guest

## Que hace

Define el alcance de funcionalidades publicas. Guest existe para que una empresa exponga servicios a visitantes y clientes finales sin entrar al panel System.

## Rutas actuales

1. `{company_slug}/home`
2. `{company_slug}/book_complaints`
3. `{company_slug}/tracking_attendances`
4. `{company_slug}/biometric_devices`

## Reglas

- La empresa siempre se deriva de `company_slug`.
- El visitante no debe enviar ni controlar `company_id`.
- Toda sucursal recibida debe validarse contra la empresa.
- No exponer datos internos.

## Mejoras sugeridas

- Crear rate limiting por ruta publica.
- Agregar tokens firmados para asistencia publica.
- Documentar contratos de payload.

