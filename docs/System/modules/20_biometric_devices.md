# 20 - Dispositivos biométricos

## Backend

- Marca y modelo son catálogos separados por empresa.
- Cada dispositivo pertenece a empresa, sucursal y modelo.
- Al crearlo se generan `access_key` y secreto; el secreto se devuelve una sola vez y se almacena cifrado.
- `rotateCredentials` invalida las credenciales anteriores y conserva fecha de rotación.
- `last_seen_at` registra el último evento procesado.
- `biometric_device_events` conserva eventos idempotentes, intentos, error y estado.
- Clientes y colaboradores usan tablas de huellas separadas, pero comparten la reserva de `device_user_id`.

## Endpoint

El contrato firmado se documenta en `docs/Guest/modules/04_biometric_devices.md`.

## Criterio operativo

El dispositivo no se crea automáticamente como activo patrimonial. Esa decisión permanece explícita porque no todo equipo integrado pertenece a la empresa; puede ser alquilado o administrado por un tercero. Si se activa en el futuro, debe gobernarse mediante `company_settings` y enlazar el activo creado.

Los pendientes visuales están en `docs/UI_UX_PENDING.md`.
