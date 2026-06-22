# Configuracion financiera: tributos y metodos de pago

## Objetivo

Permitir que cada empresa configure tributos y metodos de pago de forma independiente para ventas y compras, sin dejar porcentajes, cargos o medios de pago fijos dentro de los modulos operativos.

## Alcance funcional

- Una venta aplica automaticamente todos los tributos activos configurados para `sale` o `both`.
- Una compra aplica automaticamente todos los tributos activos configurados para `purchase` o `both`.
- Los tributos de venta y compra se registran como filas independientes. Si un concepto aplica a ambos ambitos, debe existir una fila `sale` y otra `purchase` para conservar configuracion, codigo y trazabilidad por modulo.
- Los tributos iniciales de venta son `SALE-IGV` / `IGV` al 18%, calculo porcentual, operacion de suma y obligatorio; y `SALE-ICBP` / `ICBP` con monto fijo de 0.50, operacion de suma y opcional.
- Los tributos iniciales de compra son `PURCHASE-IGV` / `IGV` al 18%, calculo porcentual, operacion de suma y obligatorio; y `PURCHASE-ICBP` / `ICBP` con monto fijo de 0.50, operacion de suma y opcional.
- En ventas y POS, un producto, servicio o membresia con `price_includes_tax = true` ya contiene IGV en su precio y no recibe IGV adicional.
- En ventas y POS, un producto, servicio o membresia con `price_includes_tax = false` forma parte de la base gravable y recibe todos los tributos activos del alcance correspondiente.
- Una venta puede registrar uno o mas metodos de pago configurados para `sale` o `both`.
- Una compra puede registrar uno o mas metodos de pago configurados para `purchase` o `both`.
- Los metodos de pago pueden exigir referencia, por ejemplo tarjeta, transferencia o billetera digital.
- El total de pagos debe cuadrar con el total del documento para evitar diferencias contables.

## Tablas

### taxes

Catalogo configurable de tributos por empresa.

Campos principales:

- `company_id`: empresa propietaria de la configuracion.
- `code`: codigo interno legible del tributo.
- `name`: nombre mostrado al usuario. En la interfaz se debe mostrar este nombre, por ejemplo `IGV`, no una etiqueta generica como `Impuestos`.
- `description`: explica el ambito del tributo, a quien aplica, de donde proviene y como debe interpretarse.
- `rate`: valor usado para calcular el tributo. Si `calculation_type = percentage`, representa porcentaje; si `calculation_type = fixed`, representa monto fijo.
- `calculation_type`: define si el valor se calcula como `percentage` o como `fixed`.
- `operation_type`: define si el resultado suma (`addition`) o resta (`subtraction`) al total del documento.
- `scope`: define si aplica a `sale`, `purchase` o `both`.
- `is_required`: define si el tributo se aplica siempre en su alcance. Si es `false`, el usuario puede marcarlo o no desde el frontend del documento.
- `is_default`: ordena primero el tributo preferente en configuraciones, reportes y futuras pantallas administrativas. No limita el calculo: ventas y compras aplican todos los tributos activos del alcance correspondiente.
- `status`: controla si el tributo esta disponible.

### payment_methods

Catalogo configurable de metodos de pago por empresa.

Campos principales:

- `company_id`: empresa propietaria de la configuracion.
- `code`: codigo interno del metodo.
- `name`: nombre mostrado al usuario.
- `scope`: define si aplica a `sale`, `purchase` o `both`.
- `requires_reference`: obliga a registrar referencia cuando corresponda.
- `is_default`: metodo sugerido por defecto.
- `status`: controla si el metodo esta disponible.

### sale_taxes / purchase_taxes

Guardan la foto del tributo aplicado al documento.

Se guarda `name`, `description`, `rate`, `calculation_type`, `operation_type`, `base_amount` y `amount` para conservar trazabilidad aunque luego cambie la configuracion del tributo.

### sale_payments / purchase_payments

Guardan los pagos aplicados al documento.

Se guarda `name`, `amount`, `reference` y `note` para conservar trazabilidad aunque luego cambie la configuracion del metodo de pago.

## Reglas de negocio

- Los tributos obligatorios no se seleccionan desde el documento: siempre se aplican.
- Los tributos opcionales se muestran en el frontend como `Impuestos extras` y solo se aplican cuando el usuario los marca.
- Los tributos obligatorios no aparecen en `Impuestos extras`; se calculan de forma silenciosa y se muestran por nombre en el resumen del documento.
- Los tributos porcentuales se calculan sobre la base imponible.
- Los tributos fijos aplican su valor como monto del documento. No dependen de una base porcentual; si el tributo es obligatorio o el usuario lo selecciona, suma o resta su monto configurado.
- Los tributos fijos opcionales permiten indicar cantidad de aplicacion como numero entero. Ejemplo: `ICBP` con valor 0.50 y cantidad 2 suma 1.00 al total.
- En frontend, al marcar un tributo fijo opcional la cantidad inicia en 1; al desmarcarlo se oculta el campo y la cantidad local vuelve a 0. Solo se envia al backend si el tributo esta seleccionado.
- Los tributos porcentuales no tienen cantidad manual; siempre se calculan sobre la base correspondiente.
- `ICBP` es un tributo fijo opcional: solo se suma cuando el usuario lo marca porque la operacion incluye bolsa gravada.
- `operation_type = addition` suma al total.
- `operation_type = subtraction` descuenta del total y guarda el monto con signo negativo.
- Los tributos multiples se calculan de forma independiente sobre la misma base. No hay calculo en cascada por ahora.
- En ventas y POS, si el item incluye IGV, el IGV se calcula como contenido dentro del precio y no incrementa el total. Si el item no incluye IGV, el IGV incrementa el total.
- Los metodos de pago seleccionados deben pertenecer a la empresa y estar activos.
- Cada metodo de pago debe indicar su importe.
- Si un metodo requiere referencia, el backend debe rechazar el documento si no se envia.
- Si se envian pagos manuales, la suma debe coincidir con el total del documento.
- Si no se envian pagos y existe un metodo por defecto, el backend puede registrar un pago automatico por el total.

## Servicios

### CompanyReferenceDataService

Expone catalogos filtrados por empresa y alcance:

- `taxesFor("sale")`
- `taxesFor("purchase")`
- `paymentMethodsFor("sale")`
- `paymentMethodsFor("purchase")`

### CommercialDocumentSettlementService

Centraliza el calculo y validacion de:

- tributos del documento.
- pagos del documento.
- cuadratura entre total y pagos.

Esto evita duplicar reglas entre ventas, POS, compras y futuros documentos.

## Modulos impactados

- Ventas: subtotal, tributos por nombre, total y pagos.
- Venta POS: calcula IGV igual que una venta normal y muestra cada tributo por su nombre.
- Compras: subtotal, tributos por nombre, total y pagos.
- Catalogo comercial: `price_includes_tax` define si el precio publico ya incluye IGV.
- Configuracion futura: se recomienda crear un modulo visual para administrar tributos y metodos de pago por empresa.

## Pendientes recomendados

- Crear pantalla de configuracion financiera para tributos.
- Crear pantalla de configuracion financiera para metodos de pago.
- Permitir distribuir manualmente montos entre varios metodos de pago desde ventas.
- Permitir distribuir manualmente montos entre varios metodos de pago desde compras.
- Agregar reportes por tributo y metodo de pago.
- Definir si algunos tributos deben calcularse en cascada en una fase futura.
