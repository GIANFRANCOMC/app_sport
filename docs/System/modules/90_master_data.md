# 90 - Maestros generales

## Qué hace

Agrupa entidades técnicas de soporte usadas por múltiples módulos.

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
- Cambios afectan usuarios, clientes, empresas, ventas, compras, caja y reportes.
- `company_settings` no debe almacenar secretos, tokens ni credenciales; esos valores pertenecen al entorno seguro.
- Tributos y métodos de pago usan `code + scope` como identidad lógica para separar ventas y compras.

## Pantalla administrativa

- Ruta UI: `/master-data`.
- Entrada de menú: Configuración -> Maestros internos.
- Recursos administrables: configuraciones, tributos, métodos de pago, documentos de identidad, tipos de comprobante y monedas.
- Cada recurso muestra tipo, alcance e impacto antes de editar o inactivar.
- La inactivación usa confirmación accesible con descripción del alcance operativo y del impacto esperado.
- Los `vue-select` de esta pantalla muestran `Sin opciones disponibles` para evitar textos residuales de librería.
- Los métodos de pago permiten imagen referencial; el backend guarda el archivo bajo storage del tenant.

## Estado de mejoras

- `MasterDataService` y `/master-data/{resource}` permiten listar, crear y modificar los maestros soportados por empresa.
- Inactivar se bloquea cuando existen referencias operativas.
- Las mutaciones invalidan maestros cacheados y todos los `initParams` dependientes.
- `MasterDataCodes` centraliza DNI, RUC, CE, boleta, factura y PEN para evitar literales dispersos.
- Los datos iniciales permanecen versionados en la migración exclusiva de inserts.

## Pendientes sugeridos

- Agregar auditoría visual por registro en la UI cuando se consolide el módulo de auditorías.
- Incorporar filtros por estado y alcance cuando crezca el volumen de maestros.
