# 04 - Biometria publica o expuesta

## Que hace

Espacio para endpoints o servicios expuestos relacionados a dispositivos biometricos. Debe tratarse como superficie publica sensible.

## Archivos

- Ruta: `routes/Guest/BiometricDevice.php`
- Tablas: `biometric_devices`, `customer_biometric_fingerprints`, `customers`, `attendances`

## Reglas

- No aceptar eventos sin autenticacion por dispositivo.
- Validar empresa, dispositivo y sucursal.
- Registrar logs de eventos.
- Reutilizar reglas de asistencia para validar membresia.

## Mejoras sugeridas

- Definir payload oficial del dispositivo.
- Agregar token por dispositivo.
- Agregar tabla de logs biometricos.
- Documentar errores y reintentos.

