# 75 - Servicios En Curso

## Que Hace

Controla atenciones con inicio y fin reales para barberias, salones, clinicas, talleres, canchas, alquileres, restaurantes y otros rubros. Una sesion puede tener cita, cola, estacion, cliente, responsable y varios detalles independientes.

## Backend

- Tablas principales: `service_sessions`, `service_session_items`.
- Trazabilidad: `service_session_events`.
- Pausas: `service_session_pauses`.
- Recursos fisicos: `service_floors`, `service_stations`.
- El controlador usa FormRequests por accion mutable: `StoreServiceFloorRequest`, `StoreServiceStationRequest`, `UpdateServiceStationLayoutRequest`, `OpenServiceSessionRequest`, `AddServiceSessionItemRequest`, `ReassignServiceSessionRequest`, `PauseServiceSessionRequest`, `CancelServiceSessionRequest` y `UpdatePreparationStatusRequest`.
- Las respuestas de acciones mutables usan `ApiResponse` para conservar estructura `bool`, `msg` y `data`.

## Reglas

- `scheduled_at`, `expected_end_at`, `tolerance_minutes` y `queue_code` soportan agenda y cola sin confundirlas con el inicio real.
- Cada pausa conserva responsable, motivo, duracion y detalle afectado; su tiempo se separa del tiempo efectivo.
- Reasignar operador registra usuario anterior, nuevo usuario, actor y motivo.
- Cancelar exige motivo y genera un evento inmutable.
- Cada detalle conserva nombre, tipo y precio historicos.
- Los detalles de preparacion disponen de estados pendiente, preparando, listo y entregado.
- Todo acceso se limita por empresa y alcance de sucursal.
- Pausas y detalles se consultan y actualizan con `company_id + service_session_id + id`, incluso dentro de la transaccion, para impedir cruces entre sesiones.

## Endpoints Adicionales

- `sessions/{id}/reassign`
- `sessions/{id}/pause`
- `sessions/{id}/resume`
- `sessions/{id}/cancel`
