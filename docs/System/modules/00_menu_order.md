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
7. `sc_items` - Catalogo comercial e inventario.
8. `sc_infrastructure` - Infraestructura.
9. `sc_configuration` - Configuracion.
10. `sc_reports` - Reportes.

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
4. `cash_registers.index` - Operacion / Cajas.
5. `sales.index` - Ventas / Listado.
6. `sales.create` - Ventas / Nuevo.
7. `purchases.index` - Compras.
8. `suppliers.index` - Proveedores.
9. `customers.index` - Clientes.
10. `tracking_customers.index` - Historial de cliente.
11. `tracking_subscriptions.index` - Membresias de clientes.
12. `tracking_attendances.index` - Asistencias.
13. `tracking_notifications.index` - Notificaciones.
14. `book_complaints.index` - Libro de reclamaciones y sugerencias.
15. `products.index` - Productos.
16. `services.index` - Servicios.
17. `subscriptions.index` - Membresias de catalogo.
18. `categories.index` - Categorias.
19. `brands.index` - Marcas.
20. `stocks_management.index` - Inventario.
21. `branches.index` - Sucursales.
22. `assets.index` - Activos.
23. `assets_management.index` - Gestion de activos.
24. `biometric_devices.index` - Dispositivos biometricos.
25. `companies.index` - Mi empresa.
26. `users.index` - Colaboradores.
27. `roles.index` - Perfiles de acceso.
28. `reports.index` - Reportes.

## Actualizaciones recientes

- `sales.pos` sale de la cabecera `Ventas` y queda en `Operacion`.
- `cash_registers.index` queda en la misma cabecera `Operacion` para reducir clics del cajero y agrupar trabajo de mostrador.
- La cabecera visible `Operacion` usa `menu-parent-operations`.
- El grupo se ubica debajo de Dashboard mediante `companies_sub_sections.section_order = 3` para POS y Cajas.

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
