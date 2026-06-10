# System - Guía de desarrollo

## Principios

- Respetar `Controller -> Service -> Model`.
- Mantener `System` aislado de `Guest`.
- Filtrar por empresa en toda operación interna.
- Preferir cambios pequeños, verificables y compatibles con datos actuales.
- No hacer refactors globales si el requerimiento solo toca un módulo.
- Mantener nombres técnicos en inglés y textos de UI en español, con tildes y signos de apertura correctos.
- Aplicar la misma estructura de rutas, controladores, requests, servicios, Vue, CSS y documentación en módulos equivalentes.

## Al modificar un módulo CRUD

Revisar siempre:

- Ruta en `routes/System`.
- Controlador.
- Servicio principal.
- Servicio de configuración.
- FormRequests de store/update.
- Modelo y relaciones.
- Migración base correspondiente si hay cambios de esquema.
- Vista Blade si cambia entrada Vite.
- Pagina Vue y componentes usados.
- Traducciones y mensajes si aplica.
- Caché de `initParams` si el cambio afecta selects o configuración.

## Política de migraciones durante la etapa reiniciable

- Mientras el proyecto permita ejecutar `migrate:fresh`, modificar directamente la migración base propietaria de la tabla.
- No crear migraciones incrementales para añadir, retirar o alterar campos, relaciones, restricciones o índices de tablas existentes.
- Las tablas nuevas relacionadas con un módulo existente deben incorporarse en la migración base del dominio y respetar el orden de sus claves foráneas.
- Registrar menús y asignaciones iniciales en la migración maestra correspondiente, no en una migración de parche.
- Solo adoptar migraciones incrementales cuando el proyecto entre en una etapa con datos persistentes que no puedan reiniciarse, o cuando se solicite explícitamente.

## Caché de `initParams` y dependencias

- Todo `*ConfigService` debe extender `BaseConfigService`; no implementar `Cache::remember`, claves o TTL en cada módulo.
- La configuración se construye en `buildConfig(int $companyId, string $page)` y se devuelve como `stdClass`.
- Declarar `cachePages()` solamente cuando el módulo tenga más de una página de configuración.
- Una mutación no debe limpiar únicamente la caché de su propio módulo cuando el recurso alimenta selects de otros módulos.
- Usar `InitParamsCacheInvalidationService::invalidate($resource, $companyId)` después de completar correctamente la transacción.
- No invalidar caché por crear ventas, cambiar stock, cancelar asistencias o actualizar asignaciones si esos registros no forman parte de `initParams`.
- La invalidación siempre es por empresa y por recurso. No usar `Cache::flush()` para resolver dependencias funcionales.
- La matriz central vive en `app/Services/System/Base/InitParamsCacheInvalidationService.php`.
- Al añadir un nuevo `ConfigService` que consuma datos compartidos, registrarlo en la dependencia correspondiente y agregar una prueba.

Dependencias registradas:

- `brands`: Marcas y Productos.
- `categories`: Categorías, Productos, Servicios y Membresías.
- `items`: Productos, Servicios, Membresías y Ventas.
- `customers`: Clientes, Ventas y seguimientos de asistencia, clientes y membresías.
- `branches`: Sucursales, Productos, Ventas, seguimientos, dispositivos biométricos, asignación de activos y gestión de stock.
- `assets`: Activos y asignación de activos.
- `users`: Usuarios y asignación de activos.
- `biometric_devices`: Dispositivos biométricos y Clientes.

## Consultas compartidas de `initParams`

- No crear ni consumir `Model::getAll($type, $companyId)`.
- Para datos dependientes de empresa, crear una sola instancia con `CompanyReferenceDataService::for($companyId)` y usar métodos con intención explícita.
- Para monedas y tipos de documento globales, usar `MasterReferenceDataService`.
- Si se implementa mantenimiento de monedas o tipos de documento, llamar `MasterReferenceDataService::clearCache()` después de una mutación correcta.
- Si una pantalla necesita una variante nueva, agregar un método descriptivo al servicio adecuado; no introducir strings como `"default"`, `"sale"` o `"management"` para modificar consultas.
- Mantener en el método explícito los filtros de estado, orden y relaciones precargadas que necesita el consumidor.
- Registrar el `ConfigService` consumidor en `InitParamsCacheInvalidationService` cuando la nueva referencia pueda cambiar por una mutación.

Ejemplo:

```php
$references = CompanyReferenceDataService::for($companyId);

$config->brands->records = $references->brands();
$config->categories->records = $references->categories();
$config->warehouses->records = $references->stockWarehouses();
$config->currencies->records = MasterReferenceDataService::currencies();
```

## Secciones y menú

- Obtener módulos habilitados mediante `CompanySectionService::getSections($companyId)`.
- No leer ni escribir manualmente la clave de caché desde controladores, listeners o Blade.
- Las mutaciones de `CompanySubSection` invalidan el menú mediante `CompanySubSectionObserver`.
- Si se incorpora administración de `sections` o `sub_sections`, debe invalidarse el menú de las empresas impactadas.

## Interfaz y experiencia de usuario

- Mantener una interfaz seria, minimalista y coherente con el propósito operativo de System.
- Reutilizar los tokens `--br-*` y colocar los estilos del sistema en `public/System/assets/css/custom.css`.
- Evitar colores aislados que no pertenezcan al branding vigente.
- Usar iconos conocidos para acciones compactas y texto visible para comandos que puedan ser ambiguos.
- Todo botón que muestre únicamente un icono debe incluir `aria-label` y tooltip descriptivo.
- Usar `br-table-header-surface` cuando una grilla o matriz interna deba compartir exactamente la superficie mate, contraste y acento de los encabezados de tablas.
- Usar `br-label-with-help` junto con `br-field-help` para alinear títulos e iconos de ayuda sin introducir alertas de bloque.
- Los atributos secundarios relevantes de un registro pueden mostrarse con `br-entity-table__attribute`: icono contextual con tooltip y valor compacto, sin crear columnas innecesarias.
- Inicializar los tooltips con el helper compartido `Alerts.tooltips({})` después de renderizados o actualizados los controles dinámicos.
- El tooltip debe explicar la acción que ocurrirá, por ejemplo: `Agregar a favoritos` o `Quitar de favoritos`.
- Los tooltips usan globalmente `br-tooltip`: superficie secondary oscura, tipografía compacta y sombra ligera, sin animación ni retraso. No crear variantes grandes por módulo.
- Los estados `disabled` y `readonly` de `form-control`, `form-select`, addons, acciones de `input-group` y `vue-select` reutilizan la superficie neutral de `br-branding.css`; deben conservar contraste, opacidad completa, cursor no permitido y cambio visual inmediato.
- Los `vue-select` múltiples reutilizan chips secondary definidos en `br-branding.css`; no crear estilos locales por módulo para sus valores, separación o controles de eliminación.
- El layout versiona `br-branding.css` con `filemtime`; los cambios visuales globales invalidan la caché del navegador sin agregar versiones manuales.
- Mantener estados de foco visibles, áreas de interacción suficientes y navegación por teclado.
- Los formularios deben reutilizar el patrón global de `br-branding.css`: `form-control`, `form-select`, `vue-select`, `select2`, prefijos, contadores y acciones dentro de `input-group` comparten hover/foco con `--br-primary`, borde unificado y sin divisores internos innecesarios.
- Los botones y addons integrados en `input-group` deben usar altura flexible (`align-self: stretch`) y no alturas fijas, para conservar la alineación vertical con cualquier variante de input.
- Los addons anteriores o posteriores compactan únicamente el padding del campo en el borde compartido. El valor queda próximo al addon sin perder el espacio exterior normal del control.
- Los símbolos monetarios usan el elemento interno reutilizable `br-currency-prefix__symbol`: color `--br-secondary`, peso medio y escala independiente de `.input-group-text`, sin competir visualmente con el importe.
- `InputText` presenta sus límites mediante `br-character-counter`: fondo blanco integrado, ancho compacto y contenido tipográfico secundario; el estado cercano al límite cambia únicamente a warning.
- Los indicadores de `vue-select` muestran únicamente una flecha compacta: sin cápsula, borde ni fondo; conservan un lienzo SVG suficiente para no recortarse al rotar y usan secondary en reposo y primary al interactuar.
- Los `vue-select` ubicados dentro de modales deben usar `append-to-body`. Su menú reutiliza la capa global `body > .vs__dropdown-menu`, superior a Bootstrap, para no alterar el scroll del modal.
- Los `vue-select` simples mantienen una sola línea mediante elipsis. Cuando el texto seleccionado sea largo, debe exponerse completo con `title` sobre `br-select-selected-text`, sin modificar la altura del formulario.
- Filtros y modales deben compartir el mismo contrato de `vue-select`: `append-to-body`, altura estable, opciones sin scroll horizontal, elipsis mediante `br-select-option-text` y contenido completo disponible con `title`.
- Las modales CRUD reutilizan `br-entity-modal` y sus variables de espaciado/superficie; ajustar `--br-entity-modal-body-space-y`, `--br-entity-modal-content-space-x` o `--br-entity-modal-footer-bg` antes de crear variantes específicas por módulo.
- El paginador compartido debe mostrarse incluso con una sola página: `Anterior` y `Siguiente` quedan deshabilitados, y la página actual permanece visible con contraste alto.
- Revisar que textos, títulos, confirmaciones y mensajes respeten tildes, puntuación y signos de interrogación.
- Centralizar títulos, subtítulos, filtros, estados vacíos, tooltips y confirmaciones en `config.entity.page` cuando la página use la estructura de configuración por entidad.
- Evitar cadenas funcionales repetidas directamente en templates Vue; la vista debe consumir la configuración declarativa.
- Para navegación compartida entre módulos, ubicar la interfaz en el layout y comunicar actualizaciones mediante eventos con nombres bajo el prefijo `br:`.

## Al agregar campos

Pasos recomendados:

1. Crear migración nueva, no editar migraciones ya ejecutadas salvo en fase inicial controlada.
2. Actualizar `$fillable` o asignaciones controladas en servicio/modelo.
3. Actualizar casts si es fecha, boolean, decimal o json.
4. Actualizar StoreRequest y UpdateRequest.
5. Actualizar formulario Vue.
6. Actualizar listado/detalle si el campo debe verse.
7. Agregar prueba o lista de verificación manual.
8. Actualizar documentación del módulo y `TABLES.md`.

## Al modificar reglas de negocio

- Identificar efectos en otros módulos.
- Buscar transacciones existentes.
- Evitar duplicar reglas en Vue; Vue puede validar UX, pero backend decide.
- Mantener mensajes claros.
- Revisar cancelaciones, estados y auditoría.
- Documentar en `new_requirements` si la mejora todavía no se implementa.

## Clean code pragmático

Se busca mejorar sin romper el sistema:

- Extraer métodos cuando una regla se repite o se vuelve difícil de probar.
- No crear abstracciones genéricas si solo se usan una vez.
- Usar nombres explícitos antes que comentarios largos.
- Mantener comentarios solo para reglas no obvias.
- No mezclar query compleja, transformacion y respuesta en el mismo bloque si se puede separar.

## Seguridad minima esperada

- Validar pertenencia a empresa/sucursal.
- Usar `FormRequest` en mutaciones.
- Extender `CompanyFormRequest` en nuevos CRUD propiedad de una empresa.
- Usar `BelongsToCompany` para ids directos; si la empresa depende de otra tabla, configurar joins y columnas calificadas en la misma regla.
- Normalizar strings mediante `normalizedStringFields()` y no repetir `trim()` en controladores.
- Reforzar relaciones estructurales con claves foráneas.
- Mantener las reglas comerciales de unicidad en backend mediante una regla reutilizable como `UniqueInCompany`; no añadir restricciones únicas o índices compuestos solo para representar esa validación.
- Añadir una comprobación de servicio cuando la entidad pueda modificarse fuera del `FormRequest` del endpoint.
- No exponer datos de otra empresa en listados o initParams.
- No confiar en `company_id` enviado desde frontend.
- Registrar al usuario que crea, actualiza, cancela o elimina.

## Verificación recomendada

Para cada cambio:

- Probar crear/editar/listar si es CRUD.
- Probar estados límite: activo, inactivo y cancelado.
- Probar empresa/sucursal incorrecta si el endpoint recibe ids.
- Probar impacto en ventas, stock, membresías o asistencias si hay relación.
- Ejecutar pruebas automatizadas si existen o crear nuevas cuando el riesgo lo amerite.

## Documentación obligatoria por cambio

Cada implementación debe actualizar los archivos `.md` impactados:

- Archivo del modulo en `System/modules`.
- `TABLES.md` si cambian campos, tablas o relaciones.
- `ARCHITECTURE.md` o esta guia si cambia un patron transversal.
- `new_requirements` para marcar mejoras aplicadas, descartadas o pendientes.

La documentación debe describir el comportamiento final, no solamente la intención inicial.
