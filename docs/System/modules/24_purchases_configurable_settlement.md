# 24 - Compras / Liquidacion configurable

## Objetivo

Extender compras para que cada empresa pueda aplicar impuestos y métodos de pago configurables sin dejar porcentajes fijos en el formulario o en el servicio.

## Alcance

- La compra aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `purchase` o `both`.
- La compra puede recibir múltiples métodos de pago desde `payment_methods`, filtrados por alcance `purchase` o `both`, indicando el monto pagado por cada método.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `purchase_taxes`.
- Los tributos iniciales de compra son `IGV` al 18% obligatorio e `ICBP` fijo de 0.50 opcional; ambos se muestran por su nombre en el frontend y el resumen evita usar una fila generica llamada `Impuestos`.
- `taxes.calculation_type` permite porcentaje o monto fijo, y `taxes.operation_type` permite sumar o restar el monto calculado.
- `taxes.is_required` define si el tributo de compra es obligatorio. El IGV de compra es obligatorio; el ICBP de compra es opcional y el usuario lo marca solo cuando corresponde.
- Los tributos fijos de compra, como ICBP, son cargos de documento: no dependen de la base porcentual y se calculan al estar obligatorios o seleccionados.
- Los tributos fijos opcionales de compra permiten indicar cantidad entera. Ejemplo: si la compra incluye 2 bolsas gravadas, el usuario marca `ICBP` y coloca cantidad 2. Al quitar el check, el campo se oculta y no se envia el tributo.
- En compras, el bloque `Impuestos extras` muestra solo tributos opcionales. Los obligatorios, como `IGV`, se calculan automáticamente y aparecen en el resumen.
- Los pagos aplicados se guardan como foto del documento en `purchase_payments`.
- Cada método de pago conserva `payment_method_id`, `payment_method_variant_id`, nombre histórico, monto, referencia y nota. El catálogo inicial usa códigos SUNAT cuando corresponde y un icono por método para mejorar identificación visual.
- Las billeteras específicas, como `Yape`, `Plin`, `Agora PAY`, `Bim` o `IzipayYA`, se registran como variantes de `Billetera digital` en `payment_method_variants`.
- La compra soporta `payment_modality`: `paid_now` exige pago completo, `cash_on_delivery` permite saldo pendiente y `installments` aplica el recargo configurado en `company_settings.purchases.installment_extra_percentage`.
- Cuando una compra queda con saldo por modalidad `cash_on_delivery` o `installments`, el backend crea `purchase_accounts_payable`, sus cuotas en `purchase_payable_installments` y la trazabilidad de pagos iniciales en `purchase_payable_payments`.
- La logica es espejo de ventas, pero con tablas propias: ventas usa cuentas por cobrar (`sale_accounts_receivable`) y compras usa cuentas por pagar (`purchase_accounts_payable`).
- Al registrar una compra, la vista cambia al modo `Listado` y refresca los registros para evitar que el usuario crea que la compra no fue guardada.

## Vista

La vista `resources/js/System/Pages/Purchases/purchases/main.vue` incluye una seccion de liquidacion con:

- Tributos aplicados automaticamente, visibles por nombre.
- Métodos de pago con importe por cada método.
- Subtotal.
- Total de impuestos.
- Total del documento.
- Total pagado.
- Diferencia pendiente o excedente.

## Inventario

El costo de inventario se mantiene desde el costo unitario del detalle. Los impuestos documentarios no alteran el stock ni el costo promedio por si solos.

`delivery_mode = immediate` es el valor por defecto y registra la entrada completa al almacén. `delivery_mode = pending` deja la recepción pendiente para entradas parciales hasta completar el 100%.

## Estado de mejoras

- El backend exige referencia cuando `payment_methods.requires_reference` está activo y conserva nombre, importe, referencia y nota históricos.
