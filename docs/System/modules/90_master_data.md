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

## Mejoras sugeridas

- Crear CRUD interno protegido para maestros si se administraran desde UI.
- Agregar seeds versionados y documentados.
- Definir codigos constantes para DNI/RUC u otros documentos.

