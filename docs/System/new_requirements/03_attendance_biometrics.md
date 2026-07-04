# 03 - Asistencias y biometricos

## Riesgo evaluado

La asistencia es un flujo crítico compartido por System, Guest y biometría. Se revisaron límites diarios, duplicados, idempotencia y salidas involuntarias.

## Requerimientos evaluados

- Límite diario de membresía aplicado.
- Checkout automático desde QR/biometría gobernado por configuración empresarial y desactivado inicialmente.
- Namespace e imports del servicio biométrico normalizados.
- Logs idempotentes de eventos biométricos implementados con firma, intentos y errores.
- Las pruebas de check-in/check-out se añadirán únicamente cuando sean solicitadas expresamente.

## Impacto cerrado

El contrato quedó integrado entre clientes, membresías, asistencias, dispositivos y portal público.

## Estado backend

- Marca, modelo y dispositivo están separados y aislados por empresa.
- Cada evento conserva dispositivo, UUID, sujeto, fecha, payload, intentos y último error.
- La asistencia de cliente registra el dispositivo y la referencia del evento que la originó.
- `customer_attendance.biometric_duplicate_tolerance_seconds` evita lecturas repetidas.
- `customer_attendance.daily_limit_scope` permite contar el límite diario por sucursal o por empresa.
- `customer_attendance.allow_automatic_checkout` controla si QR o biometría pueden finalizar una asistencia activa; el valor inicial es `false` para evitar salidas involuntarias.
