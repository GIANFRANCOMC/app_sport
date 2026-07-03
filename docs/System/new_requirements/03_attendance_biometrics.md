# 03 - Asistencias y biometricos

## Problema

La asistencia es flujo critico y se usa desde System, Guest y biometricos. Hay reglas importantes sin pruebas visibles.

## Requerimientos sugeridos

- Tests de check-in/check-out.
- Aplicar limite diario de membresia.
- Definir si checkout automatico se permite desde QR/biometria.
- Namespace e imports del servicio biométrico normalizados.
- Logs idempotentes de eventos biométricos implementados con firma, intentos y errores.

## Impacto

Alto. Afecta clientes, membresias, asistencias, dispositivos y portal publico.

## Pendientes y mejoras por realizar

- Completar matriz marca-modelo-dispositivo y documentar compatibilidad por proveedor.
- Revisar sincronizacion y errores de dispositivos por empresa y sucursal.
