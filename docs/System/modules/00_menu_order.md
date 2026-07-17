# 00 - Orden de módulos System

Este archivo es el índice funcional y contrato de navegación de System. Refleja el orden definido para el menú, relaciona cada acceso con su ruta y permite documentar módulos aunque todavía no estén activos para una empresa.

## Para qué sirve

- Mantener un orden estable entre menú, documentación y módulos.
- Identificar qué rutas pertenecen a cada sección.
- Diferenciar módulos planificados, habilitados e implementados.
- Evitar que un cambio de UI altere sin querer la estructura funcional.
- Servir como punto de entrada antes de revisar el archivo detallado de cada módulo.

El menú real se obtiene de `sections`, `sub_sections` y `companies_sub_sections` mediante `CompanySectionService`. El layout solo renderiza lo que entrega el servicio.

## Criterio de agrupación

El menú no debe agrupar por tecnología ni por controlador, sino por intención operativa del usuario. Por eso la cabecera anterior `Operación` se elimina como grupo visible: mezclaba POS, cajas, asistencia laboral y restaurante.

La estructura recomendada separa responsabilidades:

- `Ventas`: acciones comerciales de venta, incluyendo POS.
- `Cajas`: operación financiera diaria de caja.
- `Gestión de colaboradores`: usuarios internos y asistencia laboral.
- `Restaurante y servicios`: flujos operativos por mesa, pedido o servicio en curso.
- `Inventario`: existencias, kardex, guías, traslados y valorización.

## Orden de cabeceras

1. `sc_home` - Inicio.
2. `sc_dashboard` - Dashboard.
3. `sc_sales` - Ventas.
4. `sc_cash` - Cajas.
5. `sc_purchases` - Compras.
6. `sc_customers` - Gestión de clientes.
7. `sc_staff` - Gestión de colaboradores.
8. `sc_items` - Catálogo comercial.
9. `sc_inventory` - Inventario.
10. `sc_restaurant_services` - Restaurante y servicios.
11. `sc_infrastructure` - Infraestructura.
12. `sc_configuration` - Configuración.
13. `sc_reports` - Reportes.

## Orden por empresa

- `sections.order` define el orden funcional base del sistema.
- `companies_sub_sections.section_order` permite ordenar las cabeceras por `company_id` desde BD.
- `companies_sub_sections.sub_section_order` permite ordenar los accesos dentro de cada cabecera por `company_id`.
- El frontend no ordena cabeceras ni accesos. Si una empresa no tiene valores personalizados, `CompanySectionService` usa `sections.order` y `sub_sections.order` como respaldo.
- Perfiles de acceso consume la misma respuesta de `CompanySectionService`, por lo que muestra cabeceras y módulos en el mismo orden que verá el cliente en el menú.

## Subsecciones

1. `home.index` - Inicio / Inicio.
2. `dashboard.index` - Dashboard / Dashboard.
3. `sales.pos` - Ventas / Venta POS.
4. `sales.create` - Ventas / Nuevo.
5. `sales.index` - Ventas / Listado.
6. `quotations.index` - Ventas / Cotizaciones.
7. `cash_registers.registers.index` - Cajas / Cajas.
8. `cash_registers.sessions.index` - Cajas / Aperturas y cierres.
9. `cash_registers.movements.index` - Cajas / Movimientos.
10. `cash_registers.summary.index` - Cajas / Resumen.
11. `misc_expenses.index` - Cajas / Gastos varios.
12. `purchases.list.index` - Compras / Listado.
13. `purchases.new.index` - Compras / Nuevo.
14. `suppliers.index` - Compras / Proveedores.
15. `customers.index` - Gestión de clientes / Clientes.
16. `tracking_customers.index` - Gestión de clientes / Historial.
17. `tracking_subscriptions.index` - Gestión de clientes / Membresías.
18. `tracking_attendances.index` - Gestión de clientes / Asistencias por documento.
19. `tracking_notifications.index` - Gestión de clientes / Notificaciones.
20. `book_complaints.index` - Gestión de clientes / Libro de reclamaciones y sugerencias.
21. `users.index` - Gestión de colaboradores / Colaboradores.
22. `user_attendances.index` - Gestión de colaboradores / Asistencia del personal.
23. `products.index` - Catálogo comercial / Productos.
24. `services.index` - Catálogo comercial / Servicios.
25. `subscriptions.index` - Catálogo comercial / Membresías.
26. `categories.index` - Catálogo comercial / Categorías.
27. `brands.index` - Catálogo comercial / Marcas.
28. `recipes.index` - Catálogo comercial / Recetas y platillos.
29. `stocks_management.stock.index` - Inventario / Control de stock.
30. `stocks_management.kardex.index` - Inventario / Kardex.
31. `stocks_management.transfers.index` - Inventario / Traslados.
32. `stocks_management.guides.index` - Inventario / Guías.
33. `stocks_management.valued.index` - Inventario / Kardex valorizado.
34. `restaurant_pos.index` - Restaurante y servicios / Restaurante POS.
35. `service_sessions.index` - Restaurante y servicios / Servicios en curso.
36. `branches.index` - Infraestructura / Sucursales.
37. `assets.index` - Infraestructura / Activos.
38. `assets_management.index` - Infraestructura / Gestión de activos.
39. `biometric_devices.index` - Infraestructura / Dispositivos biométricos.
40. `companies.index` - Configuración / Mi empresa.
41. `roles.index` - Configuración / Perfiles de acceso.
42. `master_data.index` - Configuración / Maestros internos.
43. `business_profile.index` - Configuración / Rubro y módulos.
44. `reports.index` - Reportes / Reportes.

## Actualizaciones recientes

- Se elimina la cabecera visible `Operación`.
- `sales.pos` vuelve a `Ventas`, junto con `Nuevo`, `Listado` y `Cotizaciones`.
- Caja se agrupa en `Cajas`: `Cajas`, `Aperturas y cierres`, `Movimientos`, `Resumen` y `Gastos varios`.
- `users.index` y `user_attendances.index` se agrupan en `Gestión de colaboradores`.
- `restaurant_pos.index` y `service_sessions.index` se agrupan en `Restaurante y servicios`.
- Inventario conserva cabecera propia para que perfiles pueda habilitar `Control de stock`, `Kardex`, `Traslados`, `Guías` y `Kardex valorizado` por separado.
- La cabecera visible `Cajas` usa `menu-parent-cash`.
- La cabecera visible `Gestión de colaboradores` usa `menu-parent-staff`.
- La cabecera visible `Restaurante y servicios` usa `menu-parent-restaurant-services`.
- `recipes.index` se mantiene en Catálogo comercial para restaurantes y negocios de comida. Permite configurar fórmulas, toppings, extras, sabores e insumos sin convertir `items.type` en un tipo nuevo.
- `tracking_attendances.index` se etiqueta como `Asistencias por documento` para diferenciarlo de asistencia laboral.
- `user_attendances.index` separa las jornadas laborales de las asistencias de clientes.
- `restaurant_pos.index` administra mesas y pedidos abiertos antes de enviarlos a Venta POS.
- `service_sessions.index` mide inicio, fin y duración de servicios, con responsable por atención y por detalle.

## Módulos técnicos de soporte

Además del menú visible, System usa entidades técnicas que también deben tratarse como módulos/documentación:

- Tipos de documento de identidad.
- Tipos de documento comercial.
- Monedas.
- Secciones y subsecciones.
- Roles y permisos por módulo.
- Preferencias de usuario.
- Series.
- Almacenes.
- Proveedores, recepciones y costos de compra.
- Emails de suscripción.
