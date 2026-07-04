# Pendientes UI/UX consolidados

## Propósito

Este es el único inventario de trabajo pendiente de frontend. Los documentos de arquitectura y módulos describen el comportamiento vigente del backend; cualquier tarea visual nueva debe agregarse aquí y retirarse cuando se implemente.

No se incluyen pruebas PHP ni reglas de negocio. Cada tarea debe consumir los permisos, alcances, validaciones y contratos HTTP existentes sin duplicar decisiones en Vue.

## Sistema visual transversal

- Crear pantallas administrativas reutilizables para `company_settings`, impuestos y métodos de pago, mostrando descripción, tipo y alcance de cada configuración.
- Mantener modales con backdrop estático, estructura `br-entity-modal`, errores por campo y acciones diferenciadas.
- Unificar tablas, filtros, estados vacíos, loaders, tooltips, exportaciones y diseño responsive con las clases `br-*` documentadas en `GENERALIDADES.md`.
- Traducir todos los mensajes residuales del navegador o de librerías y corregir textos con codificación dañada.
- Mostrar siempre el alcance operativo activo cuando una acción depende de sucursal, caja o almacén.
- Incorporar confirmaciones accesibles para acciones destructivas sin permitir Enter como aceptación accidental.

## Guest

- **Inicio público:** jerarquizar categorías, optimizar SEO y metadatos sociales, y presentar catálogo responsive sin exponer precios ocultos.
- **Libro de reclamaciones:** integrar el CAPTCHA configurado, la consulta por código de seguimiento, la carga múltiple hacia el contrato existente y la presentación clara de la respuesta pública.
- **Asistencia pública:** sustituir enlaces legacy por enlaces firmados, explicar sucursal y estado del registro, y mostrar respuestas antiabuso sin detalles internos.
- **Dispositivos biométricos:** no requiere una pantalla pública; documentar visualmente las credenciales solo en System y mostrarlas una única vez al rotarlas.

## Dashboard y Home

- **Dashboard:** diseñar cards para ventas netas, ventas canceladas, asistencias y membresías por vencer; agregar selector de fecha y sucursal respetando el alcance del usuario.
- **Dashboard:** adaptar la vista al nuevo contrato agregado, sin solicitar registros completos.
- **Home/Favoritos:** evaluar orden manual de favoritos, búsqueda por descripción y estados vacíos; conservar el panel flotante y el desenfoque actuales.

## Clientes

- **Clientes:** incorporar contacto de emergencia y observaciones médicas como bloque opcional y discreto.
- **Historial:** agregar rango personalizado, resumen financiero y resumen de asistencias con filtros por sucursal.
- **Membresías:** exponer política de solapamiento, renovación y límites diarios con mensajes claros.
- **Asistencia de clientes:** permitir correcciones auditadas, exportación y detalle del dispositivo biométrico exacto.
- **Notificaciones:** mostrar intentos, último error y ejecutar el reintento controlado mediante `PATCH /tracking_notifications/{id}/retry`; no ofrecer rutas públicas directas de envío.
- **Libro de reclamaciones interno:** separar respuesta interna y pública, visualizar historial de estados y administrar adjuntos.

## Catálogo comercial

- **Productos:** etiquetas de códigos de barras por lote, lectura por escáner en ventas y publicación en catálogo/PDF.
- **Productos:** incorporar los nuevos campos opcionales de duración, comisión, beneficios y restricciones solo cuando correspondan al tipo de ítem.
- **Servicios:** duración estimada y comisión con ayuda contextual.
- **Membresías de catálogo:** límite diario, beneficios y restricciones en una sección propia.
- **Categorías:** orden, visibilidad pública y confirmación del bloqueo cuando tenga productos activos.
- **Marcas:** carga validada de logotipo, país de origen y sitio oficial.
- **Recetas y platillos:** selector de toppings/sabores y combinaciones parciales; presentar costo teórico por almacén y mermas reales consumiendo los endpoints existentes; conectar con KDS cuando se implemente su interfaz.
- **Recetas y platillos:** mapear errores anidados de componentes, toppings y opciones al control exacto devuelto por `CompanyFormRequest`, sin reemplazarlos por una alerta genérica.
- **Activos:** categoría, código patrimonial, serie física y alta rápida de categorías.

## Ventas, POS y caja

- **Nueva venta:** administración visual de tributos opcionales, cantidades para cargos fijos y distribución de pagos.
- **Listado de ventas:** rango de fechas, sucursal y detalle de reposición al cancelar.
- **Venta POS:** mantener catálogo y resumen fijos, mostrar comprobantes disponibles, tributos por nombre y pagos solo en la confirmación.
- **Caja:** separar Cajas, Aperturas y cierres, Movimientos y Resumen en páginas independientes con filtros y exportación.
- **Caja:** exponer política empresarial de caja obligatoria y reportes por fecha, sucursal, caja, usuario, turno y método de pago.
- **Cierre principal:** presentar conteo físico de inventario, diferencias y observaciones antes de confirmar.

## Inventario y compras

- **Control de stock:** alertas automáticas de mínimo y vista consolidada entre almacenes.
- **Kardex:** filtros, exportación, valorización y explicación del costo unitario/promedio.
- **Traslados:** formulario multiproducto con almacén origen/destino y trazabilidad del responsable.
- **Guías:** pantallas para guías de entrada/salida, numeración, estado y detalles.
- **Alertas de stock:** consumir `GET /stocks_management/alerts`, diferenciar abiertas/resueltas y permitir navegar al producto/almacén sin duplicar reglas en Vue.
- **Compras:** páginas separadas para Nuevo y Listado; selector explícito de entrega inmediata o pendiente.
- **Compras:** vencimiento, aprobación, gastos distribuibles, recepción parcial y devolución a proveedor.
- **Proveedores:** múltiples contactos y cuentas bancarias, condiciones de pago, historial de compras/devoluciones y desempeño.

## Personal, perfiles y seguridad

- **Seguridad de acceso:** crear un visor del historial de autenticación que consuma `GET /users/{id}/authentication-events`, con filtros por evento, resultado y fechas.
- **Perfiles:** duplicar perfil, mostrar auditoría, resumen de usuarios afectados y advertencia al intentar retirar el último administrador.
- **Colaboradores:** contraseña en flujo separado, bloqueo sin eliminación y visualización de auditoría sensible.
- **Asistencia laboral:** horarios, pausas, tardanzas, horas ordinarias/extra y solicitudes de corrección; añadir descarga de nómina consumiendo el endpoint `export` existente.
- **Asistencias:** adaptar formularios manuales y lectores QR al contrato de validación por empresa/sucursal y conservar los mensajes por campo devueltos por backend.
- **Biométricos:** consumir la rotación de credenciales, mostrar el secreto una sola vez y visualizar el último contacto y los eventos fallidos.

## Restaurante y servicios

- **Restaurante POS:** presentar estados de cocina/KDS consumiendo la transición secuencial `pending -> preparing -> ready -> delivered` y la trazabilidad existente.
- **Restaurante POS:** representar citas programadas, aforo y elementos no operativos del plano usando sesiones, pisos y estaciones existentes.
- **Pisos y mesas:** edición de nombre, orden, fondo y disposición con validación visual de colisiones.
- **Servicios en curso:** agenda, cola, tolerancia, pausas, reasignación, cancelación y línea de tiempo inmutable.
- **Servicios en curso:** SLA, alertas, comisiones y reportes por sucursal, estación, servicio y responsable.

## Reportes y maestros

- **Reportes:** formulario estándar de parámetros, nombres de archivo consistentes, progreso y mensajes cuando una consulta exceda límites.
- **Series:** filtros de auditoría, exportación e indicadores de saltos de correlativo.
- **Reportes financieros:** consumir `/reports/settlements` para resumir tributos y métodos de pago por alcance y rango.
- **Maestros internos:** consumir el CRUD protegido `/master-data/{resource}` para tipos de identidad, documentos, monedas, tributos, métodos de pago y configuraciones, con advertencia de impacto antes de inactivar.
- **Mi empresa/Sucursales:** separar configuración pública, fiscal y operativa; coordenadas, capacidad y documentos disponibles.

## Regla de cierre

Una tarea se elimina de este archivo únicamente cuando la interfaz, estados responsive, accesibilidad, permisos y mensajes de error estén verificados. Si requiere una capacidad backend nueva, debe documentarse primero en el módulo correspondiente y no simularse desde frontend.
