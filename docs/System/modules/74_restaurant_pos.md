# 74 - Restaurante POS

## Qué hace

Administra pisos, mesas, pedidos y tiempos antes del cobro. La atención operativa no reemplaza la venta: al cobrar se genera una venta normal con comprobante, tributos, pagos, caja e inventario.

## Estructura

- `service_floors`: pisos o zonas por sucursal.
- `service_stations`: mesas y otros recursos con capacidad, posición, color y forma.
- `service_sessions`: atención activa, cita, cola y vínculo final con la venta.
- `service_session_items`: productos/servicios y estados de preparación.
- `service_session_events`: apertura, inicio, reasignación, pausa, reanudación, cancelación y cierre.
- `service_session_pauses`: pausas justificadas.

## Reglas

- Una mesa solo puede tener una atención pendiente o en curso.
- Un piso y sus mesas siempre pertenecen a la misma sucursal y empresa.
- Las coordenadas son porcentuales para conservar el plano entre resoluciones.
- La disponibilidad se deriva de sesiones abiertas; no se duplica en una columna.
- Los estados de preparación soportan recibido/pendiente, preparando, listo y entregado.
- `PATCH /service_operations/items/{id}/preparation-status` aplica únicamente la transición secuencial `pending -> preparing -> ready -> delivered`, guarda sus fechas y registra el evento.
- Cancelaciones y reasignaciones conservan actor, motivo y metadatos.
- La sesión se vincula a `sales_header` únicamente al confirmarse el cobro.
