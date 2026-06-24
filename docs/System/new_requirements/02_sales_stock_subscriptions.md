# 02 - Ventas, stock y membresias

## Problema

La venta crea membresias y descuenta stock. Actualmente puede crear stock negativo si no existe `warehouse_item` o si la cantidad no alcanza.

## Requerimientos sugeridos

- Definir politica de stock negativo por empresa.
- Revertir stock al anular venta.
- Validar serie contra sucursal y empresa.
- Bloquear correlativos duplicados por concurrencia.
- Heredar `attendance_limit_per_day` desde membresia de catalogo.

## Impacto

Alto. Afecta `SaleService`, stock, membresias y reportes.

## Pendientes y mejoras por realizar

- Consolidar reglas de venta, POS, caja, inventario y membresias en servicios compartidos.
- Agregar pruebas funcionales cuando el flujo de impuestos, pagos y stock quede estable.
