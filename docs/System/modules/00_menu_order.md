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
8. `user_attendances.index` - Operacion / Asistencia del personal.
9. `restaurant_pos.index` - Operacion / Restaurante POS.
10. `service_sessions.index` - Operacion / Servicios en curso.
11. `sales.index` - Ventas / Listado.
12. `sales.create` - Ventas / Nuevo.
13. `purchases.list.index` - Compras / Listado.
14. `purchases.new.index` - Compras / Nuevo.
15. `suppliers.index` - Compras / Proveedores.
16. `customers.index` - Clientes.
17. `tracking_customers.index` - Historial de cliente.
18. `tracking_subscriptions.index` - Membresias de clientes.
19. `tracking_attendances.index` - Asistencias.
20. `tracking_notifications.index` - Notificaciones.
21. `book_complaints.index` - Libro de reclamaciones y sugerencias.
22. `products.index` - Productos.
23. `services.index` - Servicios.
24. `subscriptions.index` - Membresias de catalogo.
25. `categories.index` - Categorias.
26. `brands.index` - Marcas.
27. `recipes.index` - Recetas y platillos.
28. `stocks_management.stock.index` - Inventario / Control de stock.
29. `stocks_management.kardex.index` - Inventario / Kardex.
30. `stocks_management.transfers.index` - Inventario / Traslados.
31. `stocks_management.guides.index` - Inventario / Guias.
32. `stocks_management.valued.index` - Inventario / Kardex valorizado.
33. `branches.index` - Sucursales.
34. `assets.index` - Activos.
35. `assets_management.index` - Gestion de activos.
36. `biometric_devices.index` - Dispositivos biometricos.
37. `companies.index` - Mi empresa.
38. `users.index` - Colaboradores.
39. `roles.index` - Perfiles de acceso.
40. `reports.index` - Reportes.

## Actualizaciones recientes

- `sales.pos` sale de la cabecera `Ventas` y queda en `Operacion`.
- Caja se divide en accesos independientes dentro de `Operacion`: `Cajas`, `Aperturas y cierres`, `Movimientos` y `Resumen`.
- Inventario se separa como cabecera propia para que perfiles pueda habilitar `Control de stock`, `Kardex`, `Traslados`, `Guías` y `Kardex valorizado` por separado.
- La cabecera visible `Operacion` usa `menu-parent-operations`.
- El grupo Operacion se ubica debajo de Dashboard mediante `companies_sub_sections.section_order = 3` para POS y Caja.
- `recipes.index` se agrega a Catalogo comercial para restaurantes y negocios de comida. Permite configurar formulas, toppings, extras, sabores e insumos sin convertir `items.type` en un tipo nuevo.
- `user_attendances.index` separa las jornadas laborales de las asistencias de clientes.
- `restaurant_pos.index` administra mesas y pedidos abiertos antes de enviarlos a Venta POS.
- `service_sessions.index` mide inicio, fin y duración de servicios, con responsable por atención y por detalle.

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
