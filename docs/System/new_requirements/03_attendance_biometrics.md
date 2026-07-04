# 03 - Asistencias y biometricos

## Problema

La asistencia es flujo critico y se usa desde System, Guest y biometricos. Hay reglas importantes sin pruebas visibles.

## Requerimientos evaluados

- Límite diario de membresía aplicado.
- Checkout automático desde QR/biometría gobernado por configuración empresarial y desactivado inicialmente.
- Namespace e imports del servicio biométrico normalizados.
- Logs idempotentes de eventos biométricos implementados con firma, intentos y errores.
- Las pruebas de check-in/check-out se añadirán únicamente cuando sean solicitadas expresamente.

## Impacto

Alto. Afecta clientes, membresias, asistencias, dispositivos y portal publico.

## Estado backend

- Marca, modelo y dispositivo están separados y aislados por empresa.
- Cada evento conserva dispositivo, UUID, sujeto, fecha, payload, intentos y último error.
- La asistencia de cliente registra el dispositivo y la referencia del evento que la originó.
- `customer_attendance.biometric_duplicate_tolerance_seconds` evita lecturas repetidas.
- `customer_attendance.daily_limit_scope` permite contar el límite diario por sucursal o por empresa.
- `customer_attendance.allow_automatic_checkout` controla si QR o biometría pueden finalizar una asistencia activa; el valor inicial es `false` para evitar salidas involuntarias.
- Los errores permanecen consultables en `biometric_device_events`; la visualización y reintento autorizado están centralizados en `docs/UI_UX_PENDING.md`.
