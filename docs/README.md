# Documentacion base de Gympe

Esta carpeta es la memoria funcional y tecnica del proyecto. Esta separada en dos dominios para no mezclar responsabilidades:

- [System](System/README.md): plataforma principal usada por empresas y sus usuarios internos.
- [Guest](Guest/README.md): portal publico usado por visitantes, clientes y servicios expuestos por System.

## Orden recomendado de lectura

1. [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md): vision general del producto.
2. [System/README.md](System/README.md): entrada a la plataforma interna.
3. [System/ARCHITECTURE.md](System/ARCHITECTURE.md): arquitectura tecnica de System.
4. [System/DEVELOPMENT_GUIDE.md](System/DEVELOPMENT_GUIDE.md): reglas de desarrollo y criterio para cambios.
5. [System/TABLES.md](System/TABLES.md): tablas usadas por System.
6. [System/modules/00_menu_order.md](System/modules/00_menu_order.md): orden oficial de modulos segun menu.
7. [Guest/README.md](Guest/README.md): entrada al portal publico.
8. [Guest/ARCHITECTURE.md](Guest/ARCHITECTURE.md): arquitectura tecnica de Guest.
9. [Guest/TABLES.md](Guest/TABLES.md): tablas usadas por Guest.
10. [REQUEST_GUIDE.md](REQUEST_GUIDE.md): como pedir cambios.

## Regla importante

No mezclar documentacion ni logica entre `System` y `Guest`.

`System` representa el sistema autenticado de la empresa: usuarios internos, configuracion, ventas, clientes, inventario, reportes y administracion.

`Guest` representa superficies publicas: visitantes, clientes finales y endpoints expuestos por una empresa mediante `company_slug`.

## Como mantener esta documentacion

- Cada modulo usable debe tener su archivo en `System/modules` o `Guest/modules`.
- Las mejoras o ideas nuevas van en `new_requirements`, no mezcladas con la descripcion actual del modulo.
- Cuando un cambio afecte tablas, actualizar `TABLES.md`.
- Cuando un cambio afecte convenciones o arquitectura, actualizar `ARCHITECTURE.md` o `DEVELOPMENT_GUIDE.md`.
- Mantener archivos numerados para que el orden de lectura y menu sea claro.
