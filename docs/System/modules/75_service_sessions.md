# 75 - Servicios en curso

## Qué hace

Controla atenciones que poseen inicio y fin reales. Sirve para barberías, salones, clínicas, talleres, canchas, alquileres, soporte técnico y cualquier negocio que necesite medir tiempo y responsable antes de cobrar.

## Arquitectura

- Vista compartida: `resources/js/System/Pages/Operations/service_operations/main.vue`.
- Ruta principal: `GET /service_operations/services`.
- Núcleo: `ServiceOperationService`.
- Tablas: `service_sessions` y `service_session_items`.
- Estación opcional: `service_stations` para sillón, cabina, habitación, cancha u otro recurso.

## Flujo

1. Se crea una atención con sucursal, cliente y responsable opcionales.
2. Puede iniciarse de inmediato o quedar pendiente.
3. Se agregan uno o varios servicios; cada uno puede tener otro colaborador.
4. El operador inicia y finaliza cada detalle.
5. La plataforma calcula duración por detalle y duración total.
6. La atención puede finalizar sin venta o enviarse a Venta POS para cobrarla.

## Reglas

- Una atención pendiente comienza automáticamente cuando inicia su primer detalle.
- Un detalle finalizado no puede reiniciarse ni volver a finalizarse.
- Finalizar toda la sesión consolida los detalles abiertos para no dejar cronómetros inconclusos.
- Los valores históricos de nombre, tipo y precio quedan en el detalle aunque luego cambie el catálogo.
- Toda lectura y escritura se limita por empresa y por alcance de sucursal del usuario.

## Indicadores posibles

- Tiempo promedio real por servicio y por colaborador.
- Diferencia entre duración configurada y duración real.
- Atenciones pendientes, en curso y finalizadas.
- Utilización de sillones, cabinas, habitaciones o canchas.
- Ventas y ticket promedio originados por cada operador.

## Pendientes y mejoras

- Agenda, citas, cola de espera y tolerancias de llegada.
- Pausas justificadas que no sumen al tiempo efectivo.
- Reasignación de operador conservando trazabilidad.
- Cancelación con motivo, aprobación y eventos inmutables.
- Comisiones por servicio, colaborador y tramo de tiempo.
- SLA, alertas por demora y notificaciones al cliente.
- Reportes exportables por sucursal, estación, servicio y responsable.
