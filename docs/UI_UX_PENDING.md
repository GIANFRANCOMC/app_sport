# Pendientes UI/UX Consolidados

## Propósito

Este archivo concentra únicamente trabajo visual pendiente. Las reglas backend, tablas, permisos y servicios vigentes viven en la documentación de cada módulo.

Cuando una pantalla queda implementada, responsive, accesible, con errores por campo y usando clases `br-*`, se retira de este archivo.

## Criterio Visual Cerrado

- Modales de trabajo con `data-bs-backdrop="static"`, estructura `br-entity-modal`, footer `br-entity-modal__footer` y cierre explícito por X o botón.
- Botones de acción diferenciados por intención: buscar, crear, editar, exportar, importar, cancelar, peligro y éxito.
- Inputs con grupos visuales unificados: prefijos, moneda, contadores, acciones laterales, errores y disabled bajo la misma línea visual.
- Selects `vue-select` con flecha, chips, clear, estados disabled y menú flotante estandarizados.
- POS con categorías sólidas, contador flotante, check activo, cards separadas por tipo y panel derecho fijo en escritorio.
- Dashboard consumiendo agregados oficiales: ventas netas, ventas anuladas, asistencias del día, membresías por vencer y sucursales activas.
- Compras con selector explícito de entrega inmediata o recepción pendiente.

## Pendientes Transversales

- Crear pantallas administrativas para `company_settings`, tributos, métodos de pago y maestros internos (`/master-data/{resource}`), mostrando descripción, tipo, alcance e impacto antes de inactivar.
- Completar revisión visual de textos residuales de librerías o navegador en pantallas no migradas.
- Agregar confirmaciones accesibles a acciones destructivas que todavía usen confirmación simple.
- Mostrar el alcance operativo activo cuando una acción dependa de sucursal, caja o almacén en pantallas legacy.

## Guest

- Inicio público: SEO, metadatos sociales y catálogo responsive respetando precios ocultos.
- Libro de reclamaciones: CAPTCHA, consulta por código, carga múltiple y respuesta pública clara.
- Asistencia pública: enlaces firmados, sucursal, estado del registro y respuestas antiabuso sin detalles internos.

## Clientes

- Contacto de emergencia y observaciones médicas como bloque opcional.
- Historial con rango personalizado, resumen financiero y resumen de asistencias.
- Membresías: solapamiento, renovación y límite diario con mensajes claros.
- Asistencia de clientes: correcciones auditadas, exportación y detalle de dispositivo.
- Reclamaciones internas: separar respuesta pública/interna, historial y adjuntos.

## Catálogo Comercial

- Productos: impresión por lote de etiquetas, lectura por escáner en ventas y publicación en catálogo/PDF.
- Servicios y membresías: duración, comisión, beneficios, restricciones y límites cuando correspondan.
- Categorías: orden, visibilidad pública y bloqueo legible cuando tenga productos activos.
- Marcas: logotipo, país de origen y sitio oficial.
- Recetas y platillos: toppings, sabores, combinaciones parciales, costo teórico, merma y conexión con KDS.
- Activos: alta rápida de categorías de activo desde la pantalla.

## Ventas, POS y Caja

- Nueva venta: terminar la visualización de tributos opcionales, cantidad para cargos fijos y pagos mixtos en todas las variantes legacy.
- Caja: separar Cajas, Aperturas y cierres, Movimientos y Resumen en páginas independientes con filtros y exportación.
- Cierre principal: conteo físico de inventario, diferencias y observaciones antes de confirmar.

## Inventario y Compras

- Control de stock: vista consolidada entre almacenes y navegación rápida a alertas entre múltiples almacenes.
- Kardex: filtros avanzados, exportación, valorización y ayuda sobre costo unitario/promedio.
- Traslados: pulir UX multiproducto con origen/destino, responsable y trazabilidad.
- Guías: pantallas de entrada/salida con numeración, estado y detalle.
- Compras: vistas dedicadas para Nuevo y Listado; la modal actual ya envía `delivery_mode`, pero falta convertir el flujo a páginas completas.
- Proveedores: contactos, cuentas bancarias, condiciones, historial y desempeño.

## Personal, Perfiles y Seguridad

- Perfiles: auditoría, usuarios afectados y advertencia al retirar el último administrador.
- Colaboradores: contraseña en flujo separado, bloqueo sin eliminación y auditoría sensible.
- Asistencia laboral: horarios, pausas, tardanzas, horas ordinarias/extra, correcciones y exportación de nómina.
- Biométricos: rotación de credenciales, secreto visible una sola vez, último contacto y eventos fallidos.

## Restaurante y Servicios

- KDS: estados `pending -> preparing -> ready -> delivered` con trazabilidad visible.
- Pisos y mesas: editor visual de nombre, orden, fondo y disposición.
- Servicios en curso: agenda, cola, tolerancia, pausas, reasignación, cancelación y línea de tiempo.
- Reportes de servicios: SLA, comisiones y métricas por sucursal, estación, servicio y responsable.

## Reportes

- Series: auditoría, exportación e indicadores de saltos de correlativo.
- Mi empresa/Sucursales: separar configuración pública, fiscal y operativa.
