# 19 - Dispositivos biometricos

## Que hace

Administra dispositivos ZKTeco K20 Pro y asociaciones de huella de clientes para asistencia biometrica.

## Archivos

- Ruta: `routes/System/Devices/BiometricDevice.php`
- Controlador: `BiometricDeviceController`
- Servicio: `BiometricDeviceService`
- Requests: `StoreBiometricDeviceRequest`, `UpdateBiometricDeviceRequest`
- Tablas: `biometric_devices`, `customer_biometric_fingerprints`

## Campos necesarios

- `company_id`
- `branch_id`
- `name`
- `brand`
- `model`
- `serial_number`
- `ip_address`
- `port`
- `device_id`
- `description`
- `status`

## Reglas

- Dispositivo pertenece a empresa y sucursal.
- Huella se asocia a cliente, dispositivo, `device_user_id` e indice de dedo.
- La huella puede vivir solo en el dispositivo.
- La asistencia biometrica debe validar membresia vigente.

## Mejoras sugeridas

- Definir endpoint seguro para eventos del dispositivo.
- Agregar firma/token por dispositivo.
- Registrar logs de eventos biometricos.
- Corregir namespace/import si hay inconsistencia con el servicio.

