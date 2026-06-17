# Configuración financiera: impuestos y métodos de pago

## Objetivo

Permitir que cada empresa configure impuestos y métodos de pago de forma independiente para ventas y compras, sin dejar porcentajes o medios de pago fijos dentro de los módulos operativos.

## Alcance funcional

- Una venta aplica automáticamente todos los impuestos activos configurados para `sale` o `both`.
- Una compra aplica automáticamente todos los impuestos activos configurados para `purchase` o `both`.
- Una venta puede registrar uno o más métodos de pago configurados para `sale` o `both`.
- Una compra puede registrar uno o más métodos de pago configurados para `purchase` o `both`.
- Los métodos de pago pueden exigir referencia, por ejemplo tarjeta, transferencia o billetera digital.
- El total de pagos debe cuadrar con el total del documento para evitar diferencias contables.

## Tablas

### taxes

Catálogo configurable de impuestos por empresa.

Campos principales:

- `company_id`: empresa propietaria de la configuración.
- `code`: código interno legible del impuesto.
- `name`: nombre mostrado al usuario.
- `rate`: porcentaje del impuesto.
- `scope`: define si aplica a `sale`, `purchase` o `both`.
- `is_default`: permite marcar impuestos sugeridos por defecto.
- `status`: controla si el impuesto está disponible.

### payment_methods

Catálogo configurable de métodos de pago por empresa.

Campos principales:

- `company_id`: empresa propietaria de la configuración.
- `code`: código interno del método.
- `name`: nombre mostrado al usuario.
- `scope`: define si aplica a `sale`, `purchase` o `both`.
- `requires_reference`: obliga a registrar referencia cuando corresponda.
- `is_default`: método sugerido por defecto.
- `status`: controla si el método está disponible.

### sale_taxes / purchase_taxes

Guardan la foto del impuesto aplicado al documento.

Se guarda `name`, `rate`, `base_amount` y `amount` para conservar trazabilidad aunque luego cambie la configuración del impuesto.

### sale_payments / purchase_payments

Guardan los pagos aplicados al documento.

Se guarda `name`, `amount`, `reference` y `note` para conservar trazabilidad aunque luego cambie la configuración del método de pago.

## Reglas de negocio

- Los impuestos se calculan sobre el subtotal del documento.
- Los impuestos no se seleccionan desde el documento: se aplican todos los activos del alcance correspondiente.
- Los impuestos múltiples se suman de forma independiente sobre la misma base.
- Los métodos de pago seleccionados deben pertenecer a la empresa y estar activos.
- Cada método de pago debe indicar su importe.
- Si un método requiere referencia, el backend debe rechazar el documento si no se envía.
- Si se envían pagos manuales, la suma debe coincidir con el total del documento.
- Si no se envían pagos y existe un método por defecto, el backend puede registrar un pago automático por el total.

## Servicios

### CompanyReferenceDataService

Expone catálogos filtrados por empresa y alcance:

- `taxesFor("sale")`
- `taxesFor("purchase")`
- `paymentMethodsFor("sale")`
- `paymentMethodsFor("purchase")`

### CommercialDocumentSettlementService

Centraliza el cálculo y validación de:

- impuestos del documento.
- pagos del documento.
- cuadratura entre total y pagos.

Esto evita duplicar reglas entre ventas, compras y futuros documentos.

## Módulos impactados

- Ventas: subtotal, impuestos, total y pagos.
- Compras: subtotal, impuestos, total y pagos.
- Configuración futura: se recomienda crear un módulo visual para administrar impuestos y métodos de pago por empresa.

## Pendientes recomendados

- Crear pantalla de configuración financiera para impuestos.
- Crear pantalla de configuración financiera para métodos de pago.
- Permitir distribuir manualmente montos entre varios métodos de pago desde ventas.
- Permitir distribuir manualmente montos entre varios métodos de pago desde compras.
- Agregar reportes por impuesto y método de pago.
- Definir si algunos impuestos deben calcularse en cascada en una fase futura.
