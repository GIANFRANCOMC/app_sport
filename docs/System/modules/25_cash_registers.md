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
- Las ventas pueden asociarse a `cash_session_id` para que sus pagos alimenten `cash_movements`.
- Los metodos de pago conservan nombre y referencia en la venta; caja usa esos datos para cuadrar efectivo, tarjeta, transferencia, billeteras digitales, Yape, Plin u otros medios configurados.
- Caja registra dinero; Kardex e inventario registran unidades fisicas.

## Menu

- Cajas pertenece a la cabecera `Operacion`, junto con `Venta POS`.
- La cabecera usa `menu-parent-operations` y se ordena por empresa con `companies_sub_sections.section_order`.
- La cabecera `Operación` expone accesos independientes para perfiles: `Cajas`, `Aperturas y cierres`, `Movimientos` y `Resumen`.

## UI implementada

- Menu `Operacion > Cajas` con acceso `cash_registers.registers.index`.
- Menu `Operacion > Aperturas y cierres` con acceso `cash_registers.sessions.index`.
- Menu `Operacion > Movimientos` con acceso `cash_registers.movements.index`.
- Menu `Operacion > Resumen` con acceso `cash_registers.summary.index`.
- Accion `Agregar caja`: permite crear varias cajas por sucursal desde el modulo Caja. El codigo interno puede dejarse vacio y se genera automaticamente con prefijo `CAJ-`.
- Las acciones `Abrir caja` y `Cerrar caja` se muestran por cada caja del listado. No existen botones globales para evitar abrir o cerrar una caja equivocada.
- Entrada `Venta POS` dentro de ventas usando el flujo de nueva venta, pero con titulo y menu propios.
- Pestañas de Caja:
  - `Cajas`: estado de cada caja, sesion activa y monto esperado.
  - `Aperturas y cierres`: historial de sesiones, esperado, contado y diferencia.
  - `Resumen`: totales y desglose por metodo de pago.
  - `Movimientos`: trazabilidad de apertura, ventas, ajustes y cierre.
- Accion `Registrar movimiento`: permite ingresos, salidas y ajustes manuales sobre una caja abierta, y solo se muestra dentro de la sección **Movimientos**.
- Accion `Descargar`: exporta movimientos filtrados en CSV compatible con Excel.
- Modales con `data-bs-backdrop="static"` y `data-bs-keyboard="false"` para evitar cierre accidental.

## Backend implementado

- `routes/System/Finance/CashRegister.php`
- `app/Http/Controllers/System/Finance/CashRegisterController.php`
- `app/Services/System/Finance/CashRegisterConfigService.php`
- `app/Services/System/Finance/CashRegisterService.php`
- `resources/js/System/Helpers/Requests.js` expone rutas especiales: `sessions`, `movements`, `summary`, `open`, `close`.
- `movement`: registra ingresos, salidas y ajustes manuales.
- `export`: descarga movimientos de caja.

## Alta de cajas por sucursal

- `cash_registers.store` crea una caja asociada a una sucursal activa.
- Si el codigo interno se deja vacio, `CashRegisterService` genera un codigo unico con prefijo `CAJ-`.
- Al crear una caja se invalidan las caches operativas de Caja y Venta POS para que la nueva caja quede disponible sin esperar al TTL.

## Pendiente

- Restringir ventas segun politica de empresa: permitir vender sin caja abierta o exigir caja abierta.
- Reporte de caja por rango de fechas, sucursal, caja, usuario y metodo de pago.
- Exportar resumen y sesiones a Excel con formato visual enriquecido.
- Caja por usuario/turno cuando una misma caja fisica sea compartida por varios colaboradores en el dia.
## Alcance operativo

- Caja aplica el alcance efectivo de perfil y colaborador para sucursales y cajas.
- Un colaborador sin selección propia hereda el perfil; no obtiene acceso total automáticamente.
- Solo puede listar, abrir, cerrar o usar cajas incluidas en `role_cash_registers`/`user_cash_registers` y en sus sucursales permitidas.
- Venta POS reutiliza esta misma restricción para evitar que un usuario cobre en una caja de una sucursal que no le corresponde.
- Los accesos `Cajas`, `Aperturas y cierres`, `Movimientos` y `Resumen` conservan permisos por acción independientes aunque compartan controlador.
