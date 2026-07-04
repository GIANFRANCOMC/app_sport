# 24 - Compras / Liquidacion configurable

## Objetivo

Extender compras para que cada empresa pueda aplicar impuestos y metodos de pago configurables sin dejar porcentajes fijos en el formulario o en el servicio.

## Alcance

- La compra aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `purchase` o `both`.
- La compra puede recibir multiples metodos de pago desde `payment_methods`, filtrados por alcance `purchase` o `both`, indicando el monto pagado por cada metodo.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `purchase_taxes`.
- Los tributos iniciales de compra son `IGV` al 18% obligatorio e `ICBP` fijo de 0.50 opcional; ambos se muestran por su nombre en el frontend y el resumen evita usar una fila generica llamada `Impuestos`.
- `taxes.calculation_type` permite porcentaje o monto fijo, y `taxes.operation_type` permite sumar o restar el monto calculado.
- `taxes.is_required` define si el tributo de compra es obligatorio. El IGV de compra es obligatorio; el ICBP de compra es opcional y el usuario lo marca solo cuando corresponde.
- Los tributos fijos de compra, como ICBP, son cargos de documento: no dependen de la base porcentual y se calculan al estar obligatorios o seleccionados.
- Los tributos fijos opcionales de compra permiten indicar cantidad entera. Ejemplo: si la compra incluye 2 bolsas gravadas, el usuario marca `ICBP` y coloca cantidad 2. Al quitar el check, el campo se oculta y no se envia el tributo.
- En compras, el bloque `Impuestos extras` muestra solo tributos opcionales. Los obligatorios, como `IGV`, se calculan automáticamente y aparecen en el resumen.
- Los pagos aplicados se guardan como foto del documento en `purchase_payments`.

## Vista

La vista `resources/js/System/Pages/Purchases/purchases/main.vue` incluye una seccion de liquidacion con:

- Tributos aplicados automaticamente, visibles por nombre.
- Metodos de pago con importe por cada metodo.
- Subtotal.
- Total de impuestos.
- Total del documento.
- Total pagado.
- Diferencia pendiente o excedente.

## Inventario

El costo de inventario se mantiene desde el costo unitario del detalle. Los impuestos documentarios no alteran el stock ni el costo promedio por si solos.

## Estado de mejoras

- El backend exige referencia cuando `payment_methods.requires_reference` está activo y conserva nombre, importe, referencia y nota históricos.
- La pantalla administrativa para impuestos y métodos de pago está centralizada en `docs/UI_UX_PENDING.md`.
