# 73 - Asistencias del personal

## Qué hace

Registra jornadas de colaboradores sin mezclar información laboral con las visitas de clientes. Permite conocer ingreso, salida, sede y minutos trabajados, además de resumir horas por semana.

## Archivos

- Migración: `database/migrations/2026_07_02_000001_create_user_attendances_table.php`.
- Modelo: `App\Models\System\Organizations\UserAttendance`.
- Servicio: `App\Services\System\Organizations\Users\UserAttendanceService`.
- Controlador: `UserAttendanceController`.
- Requests: `CheckInUserAttendanceRequest`, `CheckOutUserAttendanceRequest`.
- Rutas: `routes/System/Organizations/UserAttendance.php`.
- Tabla: `user_attendances`.

## Campos

- `company_id`, `branch_id` y `user_id`: empresa, sucursal y colaborador.
- `work_date`: fecha laboral derivada del ingreso.
- `checked_in_at`, `checked_out_at`: inicio y fin de la jornada.
- `worked_minutes`: duración consolidada al finalizar.
- `source_type`: `manual_form`, `qr_camera`, `qr_scanner`, `biometric` o `system`.
- `source_reference`: identificador opcional del lector, dispositivo o integración.
- `observation`, `motive`: contexto operativo o de anulación.
- `status`: `active`, `finalized` o `canceled`.
- Campos de auditoría para creación, modificación y anulación.

## Reglas

- El colaborador y la sucursal deben estar activos y pertenecer a la empresa autenticada.
- Una jornada activa es única por empresa y colaborador, no por sucursal.
- Un colaborador no puede aparecer trabajando simultáneamente en dos sedes.
- La salida debe registrarse en la misma sucursal de la jornada abierta.
- La salida debe ser posterior al ingreso.
- `worked_minutes` se calcula y almacena al finalizar para mantener reportes históricos estables.
- Los resúmenes semanales consideran únicamente jornadas `finalized`.
- El inicio de semana es lunes y el final domingo.
- Las operaciones usan transacción y bloqueo de la fila del usuario para evitar dos check-ins concurrentes.
- Los conflictos operativos, como una jornada ya abierta o una salida en otra sede, responden como validación `422` con un mensaje accionable.

## Endpoints

- `GET /user_attendances/list`: listado por colaborador, sucursal, estado y fechas.
- `GET /user_attendances/weekly-summary`: minutos y horas por día y semana.
- `POST /user_attendances/check-in`: abre jornada.
- `PATCH /user_attendances/check-out`: finaliza jornada.

Los endpoints heredan permisos del módulo `users.index`: lectura para listados/resumen y acción `operate` para ingreso/salida.

## Pruebas

`tests/Feature/AttendanceFlowsTest.php` verifica:

- Check-in y check-out laboral.
- Cálculo de minutos y horas semanales.
- Bloqueo de jornadas simultáneas en sucursales distintas.

## Mejoras futuras

- Crear la pantalla de asistencia laboral dentro de Colaboradores.
- Configurar horarios, turnos nocturnos, descansos y días no laborables.
- Separar horas ordinarias, tardanzas, horas extra y ausencias justificadas.
- Incorporar solicitudes y aprobación de correcciones sin modificar el registro original.
- Agregar una identidad biométrica para colaboradores y resolver `device_user_id` sin colisionar con clientes.
- Permitir políticas de redondeo y tolerancia configurables por empresa.
- Añadir cierres automáticos supervisados para jornadas olvidadas.
- Exportar reportes semanales y mensuales para nómina.
- Evaluar una tabla `user_attendance_breaks` para descansos múltiples dentro de una jornada.
- Definir cómo repartir horas de jornadas nocturnas que cruzan de fecha o de semana.
