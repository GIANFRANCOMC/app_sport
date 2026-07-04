# 90 - Maestros generales

## Que hace

Agrupa entidades tecnicas de soporte usadas por muchos modulos.

## Entidades

- `identity_document_types`: tipos de documento de personas.
- `document_types`: tipos de comprobante/documento comercial.
- `currencies`: monedas.
- `taxes`: tributos separados por alcance de venta o compra.
- `payment_methods`: medios de pago configurables por alcance.
- `company_settings`: políticas tipadas por grupo y clave.

## Reglas

- Deben mantenerse estables porque alimentan formularios y relaciones.
- No se eliminan cuando están referenciadas; la inactivación se bloquea en los maestros estructurales.
- Cambios afectan usuarios, clientes, empresas, ventas y reportes.

## Estado de mejoras

- `MasterDataService` y `/master-data/{resource}` permiten listar, crear y modificar tipos de identidad, documentos, monedas, tributos, métodos de pago y configuraciones por empresa.
- Inactivar se bloquea cuando existen referencias operativas.
- Código y alcance forman la identidad lógica de tributos y métodos; grupo y clave cumplen esa función en configuraciones.
- Las mutaciones invalidan maestros cacheados y todos los `initParams` dependientes.
- `company-settings` rechaza claves que representen secretos, tokens o credenciales; esos valores pertenecen al entorno seguro.
- `MasterDataCodes` centraliza DNI, RUC, CE, boleta, factura y PEN para evitar literales dispersos.
- Los datos iniciales permanecen versionados en la migración exclusiva de inserts.
