# Documentacion base de Gympe

Esta carpeta es la memoria funcional y tecnica del proyecto. Esta separada en dos dominios para no mezclar responsabilidades:

- [System](System/README.md): plataforma principal usada por empresas y sus usuarios internos.
- [Guest](Guest/README.md): portal publico usado por visitantes, clientes y servicios expuestos por System.

## Orden recomendado de lectura

1. [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md): vision general del producto.
2. [GENERALIDADES.md](GENERALIDADES.md): reglas transversales de arquitectura, UI, branding, cache, migraciones y documentacion.
3. [System/README.md](System/README.md): entrada a la plataforma interna.
4. [System/ARCHITECTURE.md](System/ARCHITECTURE.md): arquitectura tecnica de System.
5. [System/DEVELOPMENT_GUIDE.md](System/DEVELOPMENT_GUIDE.md): reglas de desarrollo y criterio para cambios.
6. [System/TABLES.md](System/TABLES.md): tablas usadas por System.
7. [System/modules/00_menu_order.md](System/modules/00_menu_order.md): orden oficial de modulos segun menu.
8. [Guest/README.md](Guest/README.md): entrada al portal publico.
9. [Guest/ARCHITECTURE.md](Guest/ARCHITECTURE.md): arquitectura tecnica de Guest.
10. [Guest/TABLES.md](Guest/TABLES.md): tablas usadas por Guest.
11. [REQUEST_GUIDE.md](REQUEST_GUIDE.md): como pedir cambios.

## Regla importante

No mezclar documentacion ni logica entre `System` y `Guest`.

`System` representa el sistema autenticado de la empresa: usuarios internos, configuracion, ventas, clientes, inventario, reportes y administracion.

`Guest` representa superficies publicas: visitantes, clientes finales y endpoints expuestos por una empresa mediante `company_slug`.

## Como mantener esta documentacion

- Cada modulo usable debe tener su archivo en `System/modules` o `Guest/modules`.
- Las propuestas backend van en `new_requirements`; al implementarse se integran al módulo y dejan de figurar como pendientes.
- Cuando un cambio afecte tablas, actualizar `TABLES.md`.
- Cuando un cambio afecte convenciones transversales, actualizar primero `GENERALIDADES.md` y luego `ARCHITECTURE.md` o `DEVELOPMENT_GUIDE.md` si aplica.
- Mantener archivos numerados para que el orden de lectura y menu sea claro.

## Criterios de mantenimiento

- Mantener `GENERALIDADES.md` como fuente transversal antes de duplicar reglas por módulo.
- Cada módulo debe referenciar tablas, rutas, reglas y estado backend vigente.
- Corregir encoding heredado al intervenir un archivo, sin mezclar cambios masivos ajenos al objetivo funcional.
- Los únicos pendientes de interfaz se concentran en [UI_UX_PENDING.md](UI_UX_PENDING.md).
