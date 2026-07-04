# 02 - Ventas, stock y membresias

## Problema

La venta crea membresias y descuenta stock. Actualmente puede crear stock negativo si no existe `warehouse_item` o si la cantidad no alcanza.

## Requerimientos evaluados

- Política de stock negativo definida por empresa.
- Reposición al anular gobernada por configuración empresarial.
- Serie validada contra sucursal y empresa.
- Correlativo protegido contra concurrencia y duplicados.
- `attendance_limit_per_day` heredado desde membresía de catálogo.

## Impacto

Alto. Afecta `SaleService`, stock, membresias y reportes.

## Estado backend

- Venta normal y POS reutilizan `SaleService`, `CommercialDocumentSettlementService` e `InventoryMovementService`.
- La política de stock negativo, reposición por anulación y caja obligatoria se resuelve desde `company_settings`.
- Serie, almacén y caja se validan contra empresa, sucursal, estado y alcance del colaborador.
- Las membresías heredan límite diario y aplican una política de solapamiento configurable.
- La renovación crea un registro nuevo enlazado mediante `renewed_from_id`; no reescribe el histórico.

Las pruebas funcionales se incorporarán cuando sean solicitadas expresamente. No constituyen un pendiente de implementación productiva en esta fase.
