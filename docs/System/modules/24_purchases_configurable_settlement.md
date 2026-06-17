# 24 - Compras / Liquidacion configurable

## Objetivo

Extender compras para que cada empresa pueda aplicar impuestos y metodos de pago configurables sin dejar porcentajes fijos en el formulario o en el servicio.

## Alcance

- La compra aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `purchase` o `both`.
- La compra puede recibir multiples metodos de pago desde `payment_methods`, filtrados por alcance `purchase` o `both`, indicando el monto pagado por cada metodo.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `purchase_taxes`.
- Los pagos aplicados se guardan como foto del documento en `purchase_payments`.

## Vista

La vista `resources/js/System/Pages/Purchases/purchases/main.vue` incluye una seccion de liquidacion con:

- Impuestos aplicados automaticamente.
- Metodos de pago con importe por cada metodo.
- Subtotal.
- Total de impuestos.
- Total del documento.
- Total pagado.
- Diferencia pendiente o excedente.

## Inventario

El costo de inventario se mantiene desde el costo unitario del detalle. Los impuestos documentarios no alteran el stock ni el costo promedio por si solos.

## Pendientes

- Agregar referencias por metodo de pago cuando la configuracion lo requiera.
- Crear pantalla administrativa para impuestos y metodos de pago por empresa.
