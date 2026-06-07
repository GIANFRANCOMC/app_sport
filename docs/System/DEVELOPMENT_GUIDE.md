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
- Migración si hay nuevos campos.
- Vista Blade si cambia entrada Vite.
- Pagina Vue y componentes usados.
- Traducciones y mensajes si aplica.
- Caché de `initParams` si el cambio afecta selects o configuración.

## Interfaz y experiencia de usuario

- Mantener una interfaz seria, minimalista y coherente con el propósito operativo de System.
- Reutilizar los tokens `--br-*` y colocar los estilos del sistema en `public/System/assets/css/custom.css`.
- Evitar colores aislados que no pertenezcan al branding vigente.
- Usar iconos conocidos para acciones compactas y texto visible para comandos que puedan ser ambiguos.
- Todo botón que muestre únicamente un icono debe incluir `aria-label` y tooltip descriptivo.
- Inicializar los tooltips con el helper compartido `Alerts.tooltips({})` después de renderizados o actualizados los controles dinámicos.
- El tooltip debe explicar la acción que ocurrirá, por ejemplo: `Agregar a favoritos` o `Quitar de favoritos`.
- Mantener estados de foco visibles, áreas de interacción suficientes y navegación por teclado.
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
