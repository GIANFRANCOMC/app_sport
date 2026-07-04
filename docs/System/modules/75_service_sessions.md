# 75 - Servicios en curso

## Qué hace

Controla atenciones con inicio y fin reales para barberías, salones, clínicas, talleres, canchas, alquileres y otros rubros. Una sesión puede tener cita, cola, estación, cliente, responsable y varios detalles independientes.

## Backend

- Tablas principales: `service_sessions`, `service_session_items`.
- Trazabilidad: `service_session_events`.
- Pausas: `service_session_pauses`.
- Recursos físicos: `service_floors`, `service_stations`.

## Reglas

- `scheduled_at`, `expected_end_at`, `tolerance_minutes` y `queue_code` soportan agenda y cola sin confundirlas con el inicio real.
- Cada pausa conserva responsable, motivo, duración y detalle afectado; su tiempo se separa del tiempo efectivo.
- Reasignar operador registra usuario anterior, nuevo usuario, actor y motivo.
- Cancelar exige motivo y genera un evento inmutable.
- Cada detalle conserva nombre, tipo y precio históricos.
- Los detalles de preparación disponen de estados pendiente, preparando, listo y entregado.
- Todo acceso se limita por empresa y alcance de sucursal.

## Endpoints adicionales

- `sessions/{id}/reassign`
- `sessions/{id}/pause`
- `sessions/{id}/resume`
- `sessions/{id}/cancel`
