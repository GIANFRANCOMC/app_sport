# 04 - Calidad de datos y campos nuevos

## Campos sugeridos

Clientes:

- Contacto de emergencia.
- Observaciones medicas.
- Fecha de inscripcion.
- Foto.

Productos:

- SKU/codigo de barras.
- Stock minimo.

Membresias:

- Limite diario configurable desde item.
- Beneficios del plan.

Activos:

- Codigo patrimonial.
- Numero de serie fisico.
- Categoria de activo.

## Impacto

Medio. Requiere migraciones, requests, formularios y documentacion por modulo.

## Estado backend

Los campos descritos ya forman parte de sus contratos de migración, modelos, requests y servicios. El criterio transversal de longitudes, `nullable`, claves foráneas y `company_id` está documentado en `docs/GENERALIDADES.md` y `docs/System/TABLES.md`.

La corrección de textos heredados con encoding dañado se realiza al tocar cada módulo, evitando reescrituras masivas de datos sin auditoría.
