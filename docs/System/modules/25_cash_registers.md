# 25 - Caja

## Que hace

Gestiona apertura, cierre, arqueo, resumen y trazabilidad de caja por sucursal. Caja no reemplaza ventas ni inventario: agrupa el dinero registrado por metodo de pago y permite cuadrar lo esperado contra lo contado.

La visualizacion principal conserva `cash_registers.index` como ruta legacy, pero el menu usa accesos independientes:

- `cash_registers.registers.index`
- `cash_registers.sessions.index`
- `cash_registers.movements.index`
- `cash_registers.summary.index`

La vista se monta desde:

- `resources/views/System/general/Finance/cash_registers/main.blade.php`
- `resources/js/System/Pages/Finance/cash_registers/main.vue`
- `resources/js/System/Pages/Finance/cash_registers/main.js`

## Tablas

- `cash_registers`: cajas configuradas por empresa y sucursal. Ejemplo: Caja principal.
- `cash_sessions`: apertura y cierre de una caja. Guarda responsable de apertura/cierre, montos esperados, contados y diferencia.
- `cash_session_payments`: resumen del arqueo por metodo de pago.
- `cash_movements`: movimientos financieros asociados a una sesion de caja. Puede registrar ventas, compras, ingresos, retiros, ajustes, apertura y cierre.
- `sale_payments`: foto de los pagos aplicados a una venta.
- `payment_methods`: catalogo configurable de metodos de pago por empresa y alcance (`sale`, `purchase`, `both`).

## Reglas

- Una sucursal puede tener una o varias cajas.
- Una caja puede tener una sesion abierta o cerrada.
- La apertura crea una sesion `open` y registra un movimiento `opening`.
- El cierre calcula el esperado desde `cash_movements`, registra el contado por metodo de pago y crea un movimiento `closing` con importe cero para trazabilidad.
- Si la caja es principal y la sucursal tiene productos inventariables, el cierre exige conteo físico de inventario antes de confirmar.
- El conteo físico compara saldo del sistema contra conteo real. Cada diferencia genera un movimiento de inventario de tipo corrección con origen `physical_count` y deja una foto en `cash_session_inventory_counts`.
- Las ventas pueden asociarse a `cash_session_id` para que sus pagos alimenten `cash_movements`.
- Los metodos de pago conservan nombre y referencia en la venta; caja usa esos datos para cuadrar efectivo, tarjeta, transferencia, billeteras digitales, Yape, Plin u otros medios configurados.
- Caja registra dinero; Kardex e inventario registran unidades fisicas.

## Menu

- Cajas pertenece a la cabecera `Cajas`, separada de Ventas y de Restaurante para que el usuario encuentre rápido apertura, arqueo y movimientos de dinero.
- La cabecera usa `menu-parent-cash` y se ordena por empresa con `companies_sub_sections.section_order`.
- La cabecera `Cajas` expone accesos independientes para perfiles: `Cajas`, `Aperturas y cierres`, `Movimientos`, `Resumen` y `Gastos varios`.

## UI implementada

- Menú `Cajas > Cajas` con acceso `cash_registers.registers.index`.
- Menú `Cajas > Aperturas y cierres` con acceso `cash_registers.sessions.index`.
- Menú `Cajas > Movimientos` con acceso `cash_registers.movements.index`.
- Menú `Cajas > Resumen` con acceso `cash_registers.summary.index`.
- Acción `Agregar caja`: permite crear varias cajas por sucursal desde el módulo Caja. El código interno puede dejarse vacío y se genera automáticamente con prefijo `CAJ-`.
- Las acciones `Abrir caja` y `Cerrar caja` se muestran por cada caja del listado. No existen botones globales para evitar abrir o cerrar una caja equivocada.
- Entrada `Venta POS` dentro de ventas usando el flujo de nueva venta, pero con título y menú propios.
- Pestañas de Caja:
  - `Cajas`: estado de cada caja, sesion activa y monto esperado.
  - `Aperturas y cierres`: historial de sesiones, esperado, contado y diferencia.
  - `Resumen`: totales y desglose por metodo de pago.
  - `Movimientos`: trazabilidad de apertura, ventas, ajustes y cierre.
- Acción `Registrar movimiento`: permite ingresos, salidas y ajustes manuales sobre una caja abierta, y solo se muestra dentro de la sección **Movimientos**.
- Acción `Descargar`: exporta movimientos filtrados en CSV compatible con Excel.
- Modales con `data-bs-backdrop="static"` y `data-bs-keyboard="false"` para evitar cierre accidental.
- Al cerrar una caja principal, la modal muestra un bloque de conteo físico con producto, almacén, saldo del sistema, conteo real, diferencia y observación por línea. El botón **Usar saldo sistema** precarga el conteo cuando no hay diferencias.
- Cada acceso de menú abre la vista correspondiente mediante `/cash_registers/page/{registers|sessions|movements|summary}`; al cambiar de sección dentro de la pantalla se actualiza la URL con `history.pushState` sin recargar.
- La cabecera de Caja es compacta: muestra la sección activa y prioriza el selector de caja de trabajo, manteniendo el contexto operativo visible.

## Backend implementado

- `routes/System/Finance/CashRegister.php`
- `app/Http/Controllers/System/Finance/CashRegisterController.php`
- `app/Services/System/Finance/CashRegisterConfigService.php`
- `app/Services/System/Finance/CashRegisterService.php`
- `StoreCashRegisterRequest`, `OpenCashSessionRequest`, `CloseCashSessionRequest` y `StoreCashMovementRequest` encapsulan validación y normalización.
- `resources/js/System/Helpers/Requests.js` expone rutas especiales: `sessions`, `movements`, `summary`, `open`, `close`.
- `movement`: registra ingresos, salidas y ajustes manuales.
- `export`: descarga movimientos de caja.
- `CashRegisterConfigService` entrega productos inventariables por almacén y sucursal permitida para preparar el conteo físico del cierre principal.
- `CashRegisterService::closeSession()` valida que una caja principal con inventario contable no cierre sin recibir conteos y sincroniza `cash_session_inventory_counts`.
- Los errores `422` usan el contrato común de `CompanyFormRequest` y no duplican validadores dentro del controlador.

## Alta de cajas por sucursal

- `cash_registers.store` crea una caja asociada a una sucursal activa.
- Si el codigo interno se deja vacio, `CashRegisterService` genera un codigo unico con prefijo `CAJ-`.
- Al crear una caja se invalidan las caches operativas de Caja y Venta POS para que la nueva caja quede disponible sin esperar al TTL.

## Estado de mejoras

- `company_settings.cash.require_open_session_on_sale` permite exigir una caja abierta de la misma sucursal.
- Abrir, cerrar y registrar movimientos valida el alcance de caja del colaborador.
- Sesiones, movimientos y resumen aceptan fecha, sucursal, caja, responsable y método de pago; el CSV conserva el mismo filtro.
- El filtro de responsable considera apertura o cierre en sesiones y responsable directo en movimientos. El filtro de método de pago restringe sesiones con arqueo/movimiento asociado y el desglose del resumen.
- El CSV usa nombre versionado `gympe-caja-movimientos-{Ymd-His}.csv` para evitar sobrescrituras ambiguas.
- Una sesión identifica turno, caja y usuario de apertura/cierre. No se permiten dos sesiones abiertas simultáneas para la misma caja física.
## Alcance operativo

- Caja aplica el alcance efectivo de perfil y colaborador para sucursales y cajas.
- La cabecera muestra `br-operational-scope` con caja y sucursal activas para que aperturas, cierres, movimientos y resúmenes no parezcan globales.
- Un colaborador sin selección propia hereda el perfil; no obtiene acceso total automáticamente.
- Solo puede listar, abrir, cerrar o usar cajas incluidas en `role_cash_registers`/`user_cash_registers` y en sus sucursales permitidas.
- Venta POS reutiliza esta misma restricción para evitar que un usuario cobre en una caja de una sucursal que no le corresponde.
- Los accesos `Cajas`, `Aperturas y cierres`, `Movimientos` y `Resumen` conservan permisos por acción independientes aunque compartan controlador.
