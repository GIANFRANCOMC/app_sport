# 90 - Maestros generales

## Que hace

Agrupa entidades tecnicas de soporte usadas por muchos modulos.

## Entidades

- `identity_document_types`: tipos de documento de personas.
- `document_types`: tipos de comprobante/documento comercial.
- `currencies`: monedas.

## Reglas

- Deben mantenerse estables porque alimentan formularios y relaciones.
- No deberian eliminarse si estan referenciadas.
- Cambios afectan usuarios, clientes, empresas, ventas y reportes.

## Estado de mejoras

- `MasterDataService` y `/master-data/{resource}` permiten listar, crear y modificar tipos de identidad, documentos y monedas por empresa.
- Inactivar se bloquea cuando existen referencias operativas.
- `MasterDataCodes` centraliza DNI, RUC, CE, boleta, factura y PEN para evitar literales dispersos.
- Los datos iniciales permanecen versionados en la migración exclusiva de inserts.
- La administración visual está en `docs/UI_UX_PENDING.md`.
