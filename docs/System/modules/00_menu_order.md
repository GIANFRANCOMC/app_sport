# 00 - Orden de modulos System

Este archivo es el indice funcional y contrato de navegacion de System. Refleja el orden definido para el menu, relaciona cada acceso con su ruta y permite documentar modulos aunque todavia no esten activos para una empresa.

## Para que sirve

- Mantener un orden estable entre menu, documentacion y modulos.
- Identificar que rutas pertenecen a cada seccion.
- Diferenciar modulos planificados, habilitados e implementados.
- Evitar que un cambio de UI altere sin querer la estructura funcional.
- Servir como punto de entrada antes de revisar el archivo detallado de cada modulo.

El menu real se obtiene de `sections`, `sub_sections` y `companies_sub_sections` mediante `CompanySectionService`. El layout solo renderiza lo que entrega el servicio.

## Orden de cabeceras

1. `sc_home` - Inicio.
2. `sc_dashboard` - Dashboard.
3. `sc_operations` - Operacion: agrupa accesos de mostrador y caja, como Venta POS y Cajas.
4. `sc_sales` - Ventas: listado y registro tradicional de ventas.
5. `sc_purchases` - Compras: documentos de compra, proveedores y recepciones.
6. `sc_customers` - Gestion de clientes.
7. `sc_items` - Catalogo comercial.
8. `sc_inventory` - Inventario: control de stock, kardex, traslados y valorizacion.
9. `sc_infrastructure` - Infraestructura.
10. `sc_configuration` - Configuracion.
11. `sc_reports` - Reportes.

## Orden por empresa

- `sections.order` define el orden funcional base del sistema.
- `companies_sub_sections.section_order` permite ordenar las cabeceras por `company_id` solo desde BD.
- `companies_sub_sections.sub_section_order` permite ordenar los accesos dentro de cada cabecera por `company_id`.
- El frontend no ordena cabeceras ni accesos. Si una empresa no tiene valores custom, `CompanySectionService` usa `sections.order` y `sub_sections.order` como respaldo.
- Perfiles de acceso consume la misma respuesta de `CompanySectionService`, por lo que muestra las cabeceras y modulos en el mismo orden que vera el cliente en el menu.

## Subsecciones

1. `home.index` - Inicio.
2. `dashboard.index` - Dashboard.
3. `sales.pos` - Operacion / Venta POS.
4. `cash_registers.registers.index` - Operacion / Cajas.
5. `cash_registers.sessions.index` - Operacion / Aperturas y cierres.
6. `cash_registers.movements.index` - Operacion / Movimientos.
7. `cash_registers.summary.index` - Operacion / Resumen.
8. `sales.index` - Ventas / Listado.
9. `sales.create` - Ventas / Nuevo.
10. `purchases.index` - Compras.
11. `suppliers.index` - Proveedores.
12. `customers.index` - Clientes.
13. `tracking_customers.index` - Historial de cliente.
14. `tracking_subscriptions.index` - Membresias de clientes.
15. `tracking_attendances.index` - Asistencias.
16. `tracking_notifications.index` - Notificaciones.
17. `book_complaints.index` - Libro de reclamaciones y sugerencias.
18. `products.index` - Productos.
19. `services.index` - Servicios.
20. `subscriptions.index` - Membresias de catalogo.
21. `categories.index` - Categorias.
22. `brands.index` - Marcas.
23. `stocks_management.stock.index` - Inventario / Control de stock.
24. `stocks_management.kardex.index` - Inventario / Kardex.
25. `stocks_management.transfers.index` - Inventario / Traslados.
26. `stocks_management.valued.index` - Inventario / Kardex valorizado.
27. `branches.index` - Sucursales.
28. `assets.index` - Activos.
29. `assets_management.index` - Gestion de activos.
30. `biometric_devices.index` - Dispositivos biometricos.
31. `companies.index` - Mi empresa.
32. `users.index` - Colaboradores.
33. `roles.index` - Perfiles de acceso.
34. `reports.index` - Reportes.

## Actualizaciones recientes

- `sales.pos` sale de la cabecera `Ventas` y queda en `Operacion`.
- Caja se divide en accesos independientes dentro de `Operacion`: `Cajas`, `Aperturas y cierres`, `Movimientos` y `Resumen`.
- Inventario se separa como cabecera propia para que perfiles pueda habilitar `Control de stock`, `Kardex`, `Traslados` y `Kardex valorizado` por separado.
- La cabecera visible `Operacion` usa `menu-parent-operations`.
- El grupo Operacion se ubica debajo de Dashboard mediante `companies_sub_sections.section_order = 3` para POS y Caja.

## Modulos tecnicos de soporte

Ademas del menu visible, System usa entidades tecnicas que tambien deben tratarse como modulos/documentacion:

- Tipos de documento de identidad.
- Tipos de documento comercial.
- Monedas.
- Secciones y subsecciones.
- Roles y permisos por modulo.
- Preferencias de usuario.
- Series.
- Almacenes.
- Proveedores, recepciones y costos de compra.
- Emails de suscripcion.
