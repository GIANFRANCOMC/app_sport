# Pendientes UI/UX Consolidados

## Propósito

Este archivo concentra únicamente trabajo visual pendiente. Las reglas backend, tablas, permisos y servicios vigentes viven en la documentación de cada módulo.

Cuando una pantalla queda implementada, responsive, accesible, con errores por campo y usando clases `br-*`, se retira de este archivo y se documenta en el módulo correspondiente.

## Criterio Visual Cerrado

- Modales de trabajo con `data-bs-backdrop="static"`, estructura `br-entity-modal`, footer `br-entity-modal__footer` y cierre explícito por X o botón.
- Botones de acción diferenciados por intención: buscar, crear, editar, exportar, importar, cancelar, peligro y éxito.
- Inputs con grupos visuales unificados: prefijos, moneda, contadores, acciones laterales, errores y disabled bajo la misma línea visual.
- Selects `vue-select` y `select2` con mensajes en español, flecha, chips, clear, estados disabled y menú flotante estandarizados.
- POS con categorías sólidas, contador flotante, check activo, cards separadas por tipo y panel derecho fijo en escritorio.
- Dashboard consumiendo agregados oficiales: ventas netas, ventas anuladas, asistencias del día, membresías por vencer y sucursales activas.
- Compras con selector explícito de entrega inmediata o recepción pendiente.
- Restaurante POS y Servicios en curso usan plano editable, KDS, línea de tiempo, agenda, cola, pausas, reasignación y métricas base.
- Maestros internos (`/master-data`) con tipo, alcance, impacto y confirmación accesible antes de inactivar.
- Mi empresa y Sucursales muestran separación visual entre configuración fiscal, pública y operativa.

## Pendientes Transversales

- Revisar pantallas antiguas que todavía dependan de assets de plantilla no usados por Vue migrado.
- Añadir el componente `br-operational-scope` en pantallas legacy cuando una acción dependa explícitamente de sucursal, caja o almacén y aún no lo muestre.

## Reportes

- Unificar visualmente filtros avanzados y exportación en reportes antiguos que todavía no usan `FiltersSection`.
