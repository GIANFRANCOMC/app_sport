# 74 - Restaurante POS

## Qué hace

Administra mesas y pedidos abiertos antes del cobro. No crea una segunda clase de venta: la atención conserva el contexto operativo y, al cobrar, genera una venta normal mediante Venta POS.

## Arquitectura

- Vista compartida: `resources/js/System/Pages/Operations/service_operations/main.vue`.
- Controlador: `ServiceOperationController`.
- Servicio: `ServiceOperationService`.
- Ruta principal: `GET /service_operations/restaurant`.
- Tablas: `service_floors`, `service_stations`, `service_sessions`, `service_session_items`.
- Integración de cobro: `sales_header` mediante `service_session_id`.

## Flujo

1. El usuario selecciona una sucursal permitida.
2. Registra uno o varios pisos por sucursal y define su orden y fondo visual.
3. Registra mesas con código, nombre, capacidad, color y forma dentro del piso correspondiente.
4. Arrastra las mesas en el plano; las coordenadas porcentuales quedan guardadas en base de datos.
5. Abre una mesa, opcionalmente asignando cliente y responsable.
6. Agrega productos o servicios durante la atención.
7. Consulta tiempo transcurrido y estado de cada detalle.
8. Envía la atención a Venta POS para completar comprobante, impuestos, pagos, caja y almacén.
9. La sesión queda finalizada y vinculada a la venta únicamente si toda la venta se confirma.

## Reglas

- Una mesa solo puede tener una atención pendiente o en curso.
- Cada piso pertenece a una única sucursal; una mesa no puede moverse hacia un piso de otra sede.
- La ubicación se guarda en porcentajes para conservar la distribución en distintos tamaños de pantalla.
- Color y forma identifican visualmente la mesa; disponible, pendiente y en atención siguen siendo estados derivados de la sesión.
- La disponibilidad se deriva de sesiones activas; no se mantiene un indicador redundante.
- El usuario solo puede operar sucursales incluidas en su alcance de acceso.
- Cliente y responsable son opcionales, pero si se envían deben estar activos y pertenecer a la empresa.
- Los precios se copian al detalle para mantener la referencia operativa previa al cobro.

## Pendientes y mejoras

- Comandas y estados de cocina: recibido, preparando, listo y entregado.
- División y unión de cuentas, cambio de mesa y pago parcial.
- Reservas, aforo y unión temporal de mesas.
- Elementos no operativos del plano: paredes, puertas, barras, escaleras y zonas restringidas.
- Edición de nombre, orden y fondo de pisos existentes.
- Modificadores, toppings y observaciones por plato desde Recetas.
- Impresión por área de preparación y pantallas KDS.
- Política de liberación de mesa cuando una venta es anulada.
- Indicadores de rotación, permanencia promedio y consumo por mesa.
