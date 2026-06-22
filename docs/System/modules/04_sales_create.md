# 04 - Ventas / Nuevo

## Que hace

Permite crear una venta con productos, servicios o membresias. Una venta puede generar efectos secundarios: descuento de stock y creacion de membresias reales.

## Archivos

- Ruta: `routes/System/Sales/Sale.php`
- Controlador: `SaleController`
- Request: `StoreSaleRequest`
- Servicio: `SaleService`
- Vista: `resources/views/System/general/Sales/sales/main.blade.php`
- Vue: `resources/js/System/Pages/Sales/sales/main.vue`
- Tablas: `sales_header`, `sales_body`, `items`, `warehouse_items`, `warehouses`, `subscriptions`

## Reglas

- Debe existir almacen para la sucursal.
- El correlativo se genera por serie.
- El total se calcula desde detalles.
- Si el detalle es `product`, se descuenta stock.
- Si el detalle es `subscription`, se crea membresia real para el cliente.
- Todo se ejecuta dentro de transaccion.
- `SaleConfigService` obtiene sucursales, clientes e ítems mediante `CompanyReferenceDataService`.
- La creación de una venta no elimina la caché de configuración: los registros operativos se consultan por endpoints separados.

## Campos necesarios

- `branch_id`
- `serie_id`
- `holder_id`
- `currency_id`
- `issue_date`
- `details[]`
- Por detalle: `item_id`, `currency_id`, `name`, `quantity`, `price`, `type`, `extras`

## Mejoras sugeridas

- La venta normal y Venta POS/Caja consultan `company_settings.inventory.allow_negative_stock_on_sale`; por defecto no permiten confirmar si algún producto supera el stock disponible del almacén seleccionado.
- La anulación aplica `company_settings.inventory.restore_stock_on_sale_cancellation`; por defecto no repone productos automáticamente. Si se requiere devolver mercadería, debe registrarse una devolución o reposición desde Inventario.
- `serie_id` debe pertenecer a la sucursal seleccionada y estar activo; si no coincide, la venta se rechaza antes de generar correlativo.
- Proteger correlativo contra concurrencia.
- Tipar `extras` de membresia con estructura clara.

## Actualizacion: impuestos y pagos configurables

- La venta ahora aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `sale` o `both`.
- La venta ahora puede recibir multiples metodos de pago desde `payment_methods`, filtrados por alcance `sale` o `both`, indicando el monto pagado por cada metodo.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `sale_taxes`.
- Los pagos aplicados se guardan como foto del documento en `sale_payments`.
- La vista `resources/js/System/Pages/Sales/sales/main.vue` muestra un bloque lateral de liquidacion con impuestos aplicados automaticamente, metodos de pago, subtotal, impuestos, total, pagado y diferencia.
- Si solo hay un metodo de pago, el importe se sincroniza con el total para facilitar el registro.
- Pendiente: crear pantalla administrativa para impuestos y metodos de pago por empresa.

## Actualizacion: IGV incluido, almacen de venta y caja

- `items.price_includes_tax` define si el precio de venta del producto, servicio o membresia ya contiene IGV.
- La venta envia `details.*.price_includes_tax` y guarda la foto del valor en `sales_body.price_includes_tax`.
- Si el precio incluye IGV, los impuestos configurados para venta no incrementan el total de ese detalle.
- Si el precio no incluye IGV, todos los impuestos activos de alcance `sale` o `both` se calculan sobre ese detalle y aumentan el total.
- Los tributos iniciales de venta son `IGV` al 18% obligatorio e `ICBP` fijo de 0.50 opcional; ambos se muestran por su nombre en el frontend y el resumen evita usar una fila generica llamada `Impuestos`.
- `taxes.calculation_type` permite porcentaje o monto fijo, y `taxes.operation_type` permite sumar o restar el monto calculado.
- `taxes.is_required` define si el tributo es obligatorio. El IGV de venta es obligatorio; el ICBP de venta es opcional y el usuario lo marca solo cuando corresponde.
- Si el precio incluye IGV, el resumen muestra el IGV contenido sin aumentar el total. Si el precio no incluye IGV, el IGV se suma al total.
- Los tributos fijos de venta, como ICBP, son cargos de documento: no dependen de la base porcentual y se calculan al estar obligatorios o seleccionados.
- Los tributos fijos opcionales de venta permiten indicar cantidad entera. Ejemplo: si la venta usa 2 bolsas, el usuario marca `ICBP` y coloca cantidad 2. Al quitar el check, el campo se oculta y no se envia el tributo.
- `sales_header.warehouse_id` guarda el almacen afectado por la venta.
- El frontend muestra el selector de almacen junto a Sucursal y Tipo de comprobante. Si la sucursal solo tiene un almacen, lo selecciona automaticamente.
- `SaleService::resolveWarehouse()` valida que el almacen pertenezca a la sucursal y a la empresa autenticada. Si hay varios almacenes y no se envia uno, el backend rechaza la venta con un mensaje accionable.
- `sales_header.cash_session_id` permite vincular la venta con una caja abierta cuando el modulo de caja este activo.
- Si la venta incluye `cash_session_id`, cada pago genera un registro en `cash_movements`, manteniendo trazabilidad por metodo de pago para apertura, cierre, arqueo y resumen de caja.
- Los metodos de pago iniciales incluyen `Efectivo`, `Tarjeta`, `Transferencia`, `Billetera digital`, `Yape` y `Plin`; todos siguen siendo configurables por empresa y por alcance (`sale`, `purchase`, `both`).
# Venta POS

- `sales.pos` se muestra bajo la cabecera `Operacion`, junto con `Cajas`, para agrupar el trabajo de mostrador debajo de Dashboard y reducir pasos operativos.
- `sales.pos` usa una vista propia tipo mostrador: categorias superiores, buscador de productos, cards de productos y ticket lateral.
- Las categorias son botones compactos sin iconos, alternando tres colores solidos: naranja operativo, gris secundario y verde de accion positiva. El contador flota sobre la esquina superior derecha y la categoria seleccionada muestra un check con fondo visible para reforzar la seleccion.
- Las cards de productos, servicios y membresias no agregan al tocar el contenido; solo agregan mediante el boton `+`.
- Cada card diferencia marca, codigo interno y codigo de barras en lineas separadas para mejorar lectura en pantallas reducidas.
- Cada card muestra una accion de detalle para abrir una modal con nombre, tipo, descripcion, marca, categorias, codigos, precio y configuracion de IGV cuando el espacio del card no alcance.
- Si no existen cajas abiertas, POS muestra una alerta roja dentro del panel de detalle y oculta cliente, pagos, limpiar venta y generar venta.
- Si existe una sola caja abierta, POS muestra caja y sucursal como texto de contexto; si existen varias, permite seleccionar la caja.
- El buscador se ubica debajo de categorias para mantener primero el contexto visual de navegacion.
- El panel derecho trabaja como ticket fijo: caja/sucursal arriba, productos agregados al centro y el bloque de subtotal, impuestos, total y revision de venta anclado al final para mantener siempre visible el cierre de la operacion.
- En escritorio, el POS divide la vista en dos columnas: catalogo a la izquierda con scroll propio y ticket a la derecha fijo. El scroll del ticket solo afecta el detalle de productos agregados, no el resumen ni el boton de revision.
- La caja activa se muestra como contexto superior del ticket con fondo anaranjado para que el cajero identifique rapidamente caja y sucursal.
- La pantalla POS reutiliza `sales.store` para generar la venta, por lo que conserva validaciones y trazabilidad del modulo de ventas.
- El ticket lateral prioriza trazabilidad de caja, detalle de productos y cierre de venta, manteniendo el boton de revision junto al resumen final.
- La caja abierta es el selector principal del POS y muestra a que sucursal pertenece. La sucursal se deriva desde la caja.
- El almacen solo se muestra cuando la sucursal tiene mas de un almacen disponible; si solo existe uno, se selecciona automaticamente.
- Los metodos de pago son multiples. Por defecto se agrega efectivo por el total de la venta; el editor vive en la modal de confirmacion y se muestra con `Cambiar metodo de pago`.
- Cuando el editor de pagos esta abierto se muestran solo inputs editables; cuando se oculta, se muestra solo el resumen por metodo de pago.
- El selector de metodo de pago es buscable para agilizar caja cuando hay varios metodos configurados.
- La venta se confirma en dos pasos: el boton `Revisar venta` abre una modal con subtotal, impuestos, total y metodos de pago editables; luego `Confirmar venta` registra la venta.
- La modal de confirmacion muestra el comprobante de pago disponible para la sucursal. Si existe mas de una serie, el usuario debe seleccionar el comprobante que corresponde, por ejemplo boleta o factura.
- POS no crea una venta simplificada ni paralela: reutiliza `sales.store`, por lo que registra `sales_header`, `sales_body`, impuestos, metodos de pago, movimientos de caja e inventario igual que una venta normal.
- POS permite agregar un cliente desde el campo Cliente reutilizando `AddCustomer`; al crear el cliente se agrega a la lista y queda disponible para seleccion.
- El boton `Generar venta` se oculta hasta que la caja, cliente, pagos y detalle esten completos, evitando una accion visualmente disponible cuando todavia falta informacion.
- Al abrir o cerrar una caja, `CashRegisterService` invalida la cache de `CashRegisterConfigService` y `SaleConfigService` para que POS muestre solo cajas abiertas vigentes al volver a ingresar.
- Los impuestos configurados para ventas se aplican automaticamente sobre productos cuyo precio no incluye IGV (`price_includes_tax = false`).
- El POS calcula subtotal, base imponible, impuestos y total con la misma regla de crear venta: los precios con IGV incluido no suman impuesto adicional; los precios sin IGV incluido reciben todos los impuestos activos de alcance `sale` o `both`.
- El POS muestra cada tributo por nombre, por ejemplo `IGV`, y soporta tributos porcentuales, fijos, de suma o de resta usando la misma regla de backend.
- En venta normal y POS, el bloque `Impuestos extras` muestra solo tributos opcionales, como `ICBP`. Los obligatorios, como `IGV`, no se seleccionan; se calculan automáticamente y aparecen en el resumen.
- Si existe una sesion de caja abierta, POS puede asociar la venta a `cash_session_id` para alimentar movimientos de caja.
## Venta POS - pagos y confirmación

- La vista principal del POS se enfoca en seleccionar catálogo, caja y revisar el detalle de la venta.
- Los métodos de pago se revisan y editan únicamente en la modal **Confirmar venta**. Por defecto se muestra un resumen legible; si el pago será mixto, el usuario puede usar **Cambiar método de pago** y ajustar importes antes de confirmar.
- El cliente también se selecciona dentro de **Confirmar venta**, junto al resumen y los pagos, para que la pantalla principal quede enfocada en armar el detalle.
- El botón **Revisar venta** queda visible cuando hay caja abierta y solo se bloquea si el detalle está vacío.
- El botón **Confirmar venta** dentro de la modal se mantiene visible y solo se bloquea si el detalle está vacío; las validaciones de cliente, caja y pagos se comunican al confirmar.
- El botón principal de la vista se mantiene como **Revisar venta** para reforzar que la venta se confirma en un segundo paso.
- Las cajas disponibles se limitan por las sucursales permitidas del colaborador. Si el usuario no tiene sucursales configuradas, mantiene acceso a todas las sucursales de la empresa.
- Los ítems con estado `inactive` no se cargan para Venta POS ni para el flujo transaccional de ventas.
