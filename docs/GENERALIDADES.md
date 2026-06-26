# Generalidades del Proyecto

Este archivo concentra criterios transversales que aplican a todos los módulos de Gympe. Sirve como memoria común para mantener coherencia funcional, visual y técnica entre `System`, `Guest`, backend, frontend, migraciones y documentación.

## Propósito

Gympe es una plataforma multiempresa orientada a operaciones comerciales y administrativas. La regla central es separar claramente:

- `System`: plataforma autenticada para empresas y colaboradores.
- `Guest`: superficies públicas para visitantes, clientes finales y recursos expuestos por empresa.

Toda implementación debe preguntarse primero si pertenece a la operación interna (`System`) o a la experiencia pública (`Guest`). No mezclar rutas, controladores, componentes ni documentación entre ambos dominios.

## Arquitectura Base

El flujo preferido en `System` es:

1. Ruta en `routes/System`.
2. Controlador en `app/Http/Controllers/System`.
3. FormRequest para validar entrada.
4. Servicio para reglas de negocio.
5. Modelo Eloquent para relaciones y persistencia.
6. ConfigService para `initParams` y referencias de pantalla.
7. Vista Blade como contenedor.
8. Entrada Vue y componentes reutilizables.
9. Documentación del módulo y tablas afectadas.

Evitar colocar reglas críticas sólo en Vue. El frontend guía al usuario; el backend decide y protege.


## Multi-tenant por BD

Gympe opera exclusivamente desde subdominios registrados y con una base de datos por cliente. El dominio raíz pertenece a otro proyecto. El registro central mínimo vive en `landlord`; la operación del cliente se ejecuta en `tenant`. La guía completa está en `System/MULTITENANT.md`.

Aunque exista una BD por cliente, `company_id` se mantiene en las tablas tenant para subcompañías internas y como defensa de aislamiento lógico. En tablas tenant, todo `company_id` debe tener FK local a `companies` salvo casos imposibles por diseño, como el registro central landlord.

La sesión también se aísla por tenant. `ResolveTenant` cambia el nombre de cookie antes de iniciar sesión y `EnsureTenantSession` invalida cualquier sesión que intente cruzar de un tenant a otro. Para producción, usar HTTPS, `SESSION_SECURE_COOKIE=true` y mantener `SESSION_DOMAIN` vacío. Ver `System/SECURITY_AND_AUTH.md`.
## Multiempresa

Toda tabla operativa, hija o maestro configurable debe tener `company_id` cuando el dato pertenece a una empresa. Esto aplica también a maestros que antes podían parecer globales, como monedas, tipos de documento o comprobantes, porque cada empresa puede tener reglas distintas.

Criterios esperados:

- No confiar en `company_id` enviado desde frontend.
- Obtener empresa desde usuario autenticado, sucursal, almacén, cabecera o middleware público.
- Validar ids con reglas reutilizables como `BelongsToCompany`.
- Usar `UniqueInCompany` para unicidad lógica por empresa cuando corresponda.
- Evitar índices explícitos salvo decisión justificada; conservar claves primarias, foráneas y `unique` cuando expresen integridad real.

## Branding

Los colores oficiales detectados del branding son:

- Primario: `#2899E5` (`--br-primary`).
- Secundario: `#1A1A35` (`--br-secondary`).
- Success: `#10b981`.
- Warning: `#f59e0b`.
- Danger: `#ef4444`.
- Info: `#0ea5e9`.
- Superficie base: `#f8fafc`.
- Superficie elevada: `#ffffff`.
- Bordes: `#e2e8f0` y `#cbd5e1`.
- Texto principal: `#1e293b`.
- Texto secundario: `#64748b`.

Los estilos compartidos deben vivir preferentemente en `public/System/assets/css/br-branding.css` o `public/System/assets/css/custom.css`, usando variables `--br-*`. Las clases nuevas reutilizables deben iniciar con `br-`.

## UI y UX

La interfaz debe sentirse operativa, seria, compacta y clara. Evitar pantallas recargadas, textos redundantes, tarjetas innecesarias y colores que compitan con la acción principal.

Criterios prácticos:

- Usar contraste suficiente, no saturación excesiva.
- Mantener formularios compactos y agrupados por intención.
- Preferir tooltips para aclaraciones breves, no alertas visuales invasivas.
- Usar labels sutiles, no grandes ni demasiado oscuros.
- Los botones deben diferenciar intención: buscar, crear, editar, importar, descargar, cancelar, confirmar y peligro.
- Los icon buttons requieren `aria-label` y tooltip.
- En desktop, botones secundarios de descarga pueden mostrarse sólo con icono si el tooltip es claro; en móvil deben mostrar icono y texto.
- Las tablas deben reutilizar la superficie global de encabezado y estados compactos.
- Los estados vacíos y loaders deben usar componentes compartidos (`WithoutData`, `Loader`) para evitar estilos distintos por módulo.

## Formularios

Todos los formularios deben reutilizar componentes y patrones existentes:

- `InputText` para texto.
- `InputNumber` para numéricos y dinero.
- `InputSlot` cuando el input necesita prefijos, sufijos, acciones, moneda o contadores.
- `vue-select` con estilos globales y `SelectNoOptions` para mensajes vacíos en español.
- `CopyButton` para copiar valores puntuales.
- `BarcodeDownloadButton` para códigos de barras descargables.

Los controles compuestos se tratan como una sola unidad visual. El hover, foco, disabled y error deben bordear el conjunto completo: input, prefijo, contador, botón lateral o selector.

Errores de campo:

- Bajo el campo, mostrar sólo el error: `Campo obligatorio.`.
- En resúmenes o modales, incluir el campo: `Nombre: Campo obligatorio.`.
- Los errores deben ser pequeños, legibles y no competir con el label.
- El borde rojo debe envolver todo el control compuesto.

## Modales y SweetAlert

Las modales de System deben usar el patrón global:

- Backdrop estático.
- No cerrar al hacer clic fuera.
- No cerrar con Escape.
- Header, body y footer con espaciado estándar.
- Botón de cerrar visible y consistente.
- Footer con superficie neutral cuando aplique.

SweetAlert debe usarse para confirmaciones, errores, success y procesos globales. No introducir delays artificiales con `setTimeout`; si hay trabajo asíncrono, mostrar loader inmediatamente.

Convención visual de SweetAlert:

- Error: borde/icono rojo, botón principal celeste.
- Warning: borde/icono amarillo, botón de confirmación danger.
- Question: borde/icono celeste, botón secondary.
- Success: borde/icono verde, botón principal celeste.
- Loading: logomark centrado con respiración sutil y texto amable.

## Cache e initParams

Los `ConfigService` preparan referencias para pantallas Vue. Deben extender `BaseConfigService` y construir datos en `buildConfig(int $companyId, string $page)`.

Reglas:

- Cache por empresa y página.
- Invalidar por recurso usando `InitParamsCacheInvalidationService`.
- No usar `Cache::flush()` para resolver dependencias funcionales.
- Si un catálogo alimenta varios módulos, invalidar todos los consumidores registrados.
- Para maestros configurables por empresa, usar servicios como `MasterReferenceDataService` con limpieza por `company_id`.

## Inventario y Trazabilidad

Las operaciones que afecten stock deben registrar movimiento. No actualizar directamente `warehouse_items.quantity`, `average_cost` o `inventory_value` desde módulos externos.

Criterios:

- Entradas, salidas, correcciones, traslados, compras, ventas, devoluciones y reposiciones deben pasar por servicio de inventario.
- El costo unitario pertenece a la trazabilidad y valorización, no al precio de venta.
- La anulación de venta no siempre repone stock; depende de `company_settings.inventory.restore_stock_on_sale_cancellation`.
- El bloqueo de stock negativo depende de configuración por empresa.
- Cuando existan varios almacenes por sucursal, la operación debe exigir almacén claro.

## Ventas, POS, Compras e Impuestos

Los impuestos son configurables por empresa, módulo y obligatoriedad. En frontend no usar el texto genérico `impuestos` cuando existe nombre fiscal como `IGV` o `ICBP`.

Criterios:

- `IGV` debe representar el impuesto principal cuando aplique.
- Los taxes pueden ser porcentaje o monto fijo.
- Un impuesto puede ser obligatorio u opcional.
- Los impuestos opcionales no porcentuales pueden requerir cantidad de aplicaciones.
- Ventas normales y POS deben compartir cálculo, trazabilidad de pagos, impuestos aplicados y generación de cabecera/detalle.
- Los métodos de pago deben guardar monto por método y sumar el total del documento.

## Migraciones

Durante la etapa reiniciable del proyecto, se puede refactorizar migraciones base. Criterios actuales:

- Separar estructura y datos iniciales.
- Crear una migración dedicada para inserts iniciales.
- Separar por dominio: maestros, empresas, catálogo, inventario, ventas, compras, caja, biometría y reportes.
- Usar `decimal(16, 4)` como estándar para cantidades y montos.
- Limitar `string` con tamaño explícito, máximo recomendado `500`; usar `text` o `longText` si corresponde.
- Agregar `company_id` donde el dato sea por empresa y declarar FK explícita a `companies`. En maestros que la empresa también referencia, permitir arranque nullable y actualizar la referencia después de sembrar maestros.
- Evitar comentarios decorativos, símbolos extraños o encoding roto.
- Tabular y espaciar consistentemente.

## Documentación

Cada cambio debe actualizar documentación afectada:

- Archivo del módulo en `docs/System/modules` o `docs/Guest/modules`.
- `TABLES.md` si cambian tablas, campos o relaciones.
- `ARCHITECTURE.md` si cambia arquitectura.
- `DEVELOPMENT_GUIDE.md` si cambia una práctica de desarrollo.
- `new_requirements` cuando quede algo pendiente o se descarte una mejora.

Formato recomendado por módulo:

1. Propósito.
2. Alcance actual.
3. Flujo funcional.
4. Archivos técnicos relacionados.
5. Tablas y relaciones.
6. Reglas de negocio.
7. Validaciones.
8. UI/UX aplicada.
9. Integraciones con otros módulos.
10. Pendientes y mejoras por realizar.

## Pendientes y Mejoras Transversales

- Limpiar comentarios con encoding dañado en encabezados CSS antiguos, especialmente en `br-branding.css`.
- Separar físicamente migraciones grandes por dominio cuando se estabilice el alcance final.
- Revisar todos los servicios para recibir `companyId` y `userId` explícitos, reduciendo dependencia directa de `Auth`.
- Completar pruebas automatizadas de ventas, POS, caja, inventario, compras e impuestos cuando se cierre la fase de cambios estructurales.
- Crear administración UI para `company_settings`, prefijos, impuestos, métodos de pago y reglas de inventario.
- Usar `php artisan company:enable {company_id}` para habilitar datos base de una empresa sin insertar manualmente tabla por tabla.
- Evolucionar permisos de `módulo` hacia `módulo + acción` sin romper perfiles actuales.
- Revisar accesibilidad de todas las modales, tooltips, selects y acciones por teclado.
- Consolidar reportes exportables por módulo con consultas reutilizadas del listado.
- Mantener una auditoría funcional clara para movimientos sensibles: stock, caja, ventas, compras, perfiles y configuración.
