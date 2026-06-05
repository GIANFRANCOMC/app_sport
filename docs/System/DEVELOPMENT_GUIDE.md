# System - Guia de desarrollo

## Principios

- Respetar `Controller -> Service -> Model`.
- Mantener `System` aislado de `Guest`.
- Filtrar por empresa en toda operacion interna.
- Preferir cambios pequenos, testeables y compatibles con datos actuales.
- No hacer refactors globales si el requerimiento solo toca un modulo.
- Mantener nombres tecnicos en ingles y textos de UI en espanol.

## Al modificar un modulo CRUD

Revisar siempre:

- Ruta en `routes/System`.
- Controlador.
- Servicio principal.
- Servicio de configuracion.
- FormRequests de store/update.
- Modelo y relaciones.
- Migracion si hay nuevos campos.
- Vista Blade si cambia entrada Vite.
- Pagina Vue y componentes usados.
- Traducciones/mensajes si aplica.
- Cache de initParams si el cambio afecta selects/configuracion.

## Al agregar campos

Pasos recomendados:

1. Crear migracion nueva, no editar migraciones ya ejecutadas salvo en fase inicial controlada.
2. Actualizar `$fillable` o asignaciones controladas en servicio/modelo.
3. Actualizar casts si es fecha, boolean, decimal o json.
4. Actualizar StoreRequest y UpdateRequest.
5. Actualizar formulario Vue.
6. Actualizar listado/detalle si el campo debe verse.
7. Agregar prueba o checklist manual.
8. Actualizar documentacion del modulo y `TABLES.md`.

## Al modificar reglas de negocio

- Identificar efectos en otros modulos.
- Buscar transacciones existentes.
- Evitar duplicar reglas en Vue; Vue puede validar UX, pero backend decide.
- Mantener mensajes claros.
- Revisar cancelaciones, estados y auditoria.
- Documentar en `new_requirements` si la mejora todavia no se implementa.

## Clean code pragmatica

Se busca mejorar sin romper el sistema:

- Extraer metodos cuando una regla se repite o se vuelve dificil de probar.
- No crear abstracciones genericas si solo se usan una vez.
- Usar nombres explicitos antes que comentarios largos.
- Mantener comentarios solo para reglas no obvias.
- No mezclar query compleja, transformacion y respuesta en el mismo bloque si se puede separar.

## Seguridad minima esperada

- Validar pertenencia a empresa/sucursal.
- Usar FormRequest en mutaciones.
- No exponer datos de otra empresa en listados o initParams.
- No confiar en `company_id` enviado desde frontend.
- Registrar usuario que crea, actualiza, cancela o elimina.

## Verificacion recomendada

Para cada cambio:

- Probar crear/editar/listar si es CRUD.
- Probar estados limite: activo/inactivo/cancelado.
- Probar empresa/sucursal incorrecta si el endpoint recibe ids.
- Probar impacto en ventas, stock, membresias o asistencias si hay relacion.
- Ejecutar pruebas automatizadas si existen o crear nuevas cuando el riesgo lo amerite.

