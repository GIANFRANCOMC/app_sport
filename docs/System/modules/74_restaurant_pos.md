# 74 - Restaurante POS

## Qué hace

Administra mesas y pedidos abiertos antes del cobro. No crea una segunda clase de venta: la atención conserva el contexto operativo y, al cobrar, genera una venta normal mediante Venta POS.

## Arquitectura

- Vista compartida: `resources/js/System/Pages/Operations/service_operations/main.vue`.
- Controlador: `ServiceOperationController`.
- Servicio: `ServiceOperationService`.
- Ruta principal: `GET /service_operations/restaurant`.
- Tablas: `service_stations`, `service_sessions`, `service_session_items`.
- Integración de cobro: `sales_header` mediante `service_session_id`.

## Flujo

1. El usuario selecciona una sucursal permitida.
2. Registra mesas o estaciones con código y capacidad.
3. Abre una mesa, opcionalmente asignando cliente y responsable.
4. Agrega productos o servicios durante la atención.
5. Consulta tiempo transcurrido y estado de cada detalle.
6. Envía la atención a Venta POS para completar comprobante, impuestos, pagos, caja y almacén.
7. La sesión queda finalizada y vinculada a la venta únicamente si toda la venta se confirma.

## Reglas

- Una mesa solo puede tener una atención pendiente o en curso.
- La disponibilidad se deriva de sesiones activas; no se mantiene un indicador redundante.
- El usuario solo puede operar sucursales incluidas en su alcance de acceso.
- Cliente y responsable son opcionales, pero si se envían deben estar activos y pertenecer a la empresa.
- Los precios se copian al detalle para mantener la referencia operativa previa al cobro.

## Pendientes y mejoras

- Comandas y estados de cocina: recibido, preparando, listo y entregado.
- División y unión de cuentas, cambio de mesa y pago parcial.
- Reservas, aforo y unión temporal de mesas.
- Modificadores, toppings y observaciones por plato desde Recetas.
- Impresión por área de preparación y pantallas KDS.
- Política de liberación de mesa cuando una venta es anulada.
- Indicadores de rotación, permanencia promedio y consumo por mesa.
