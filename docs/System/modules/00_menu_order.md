# 00 - Orden de módulos System

Este archivo es el índice funcional y contrato de navegación de System. Refleja el orden definido para el menú, relaciona cada acceso con su ruta y permite documentar módulos aunque todavía no estén activos para una empresa.

## Para qué sirve

- Mantener un orden estable entre menú, documentación y módulos.
- Identificar qué rutas pertenecen a cada sección.
- Diferenciar módulos planificados, habilitados e implementados.
- Evitar que un cambio de UI altere sin querer la estructura funcional.
- Servir como punto de entrada antes de revisar el archivo detallado de cada módulo.

El menú real se obtiene de `sections`, `sub_sections` y `companies_sub_sections` mediante `CompanySectionService`. El layout solo renderiza lo que entrega el servicio.

La fuente canónica del menú actual es `database/migrations/2026_07_18_000001_sync_system_menu_catalog.php`. Las migraciones de dominio no deben registrar, mover ni renombrar opciones del menú; si un módulo nuevo necesita navegación, se agrega en esa migración o en su sucesora directa de catálogo de menú.

## Criterio de agrupación

El menú no debe agrupar por tecnología ni por controlador, sino por intención operativa del usuario. Por eso la cabecera anterior `Operación` se elimina como grupo visible: mezclaba POS, cajas, asistencia laboral y restaurante.

La estructura recomendada separa responsabilidades:

- `POS`: puntos de venta rápidos, tanto mostrador como restaurante.
- `Ventas`: ventas documentadas, listados y cotizaciones.
- `Cajas`: operación financiera diaria de caja.
- `Atención al cliente`: flujos de contacto, servicios en curso, notificaciones, reclamaciones y asistencia pública por documento.
- `Gestión de colaboradores`: usuarios internos y asistencia laboral.
- `Restaurante y servicios`: configuración operativa de restaurante y servicios que no pertenezca al punto de venta ni a atención.
- `Inventario`: existencias, kardex, guías, traslados y valorización.

## Orden de cabeceras

1. `sc_home` - Inicio.
2. `sc_dashboard` - Dashboard.
3. `sc_pos` - POS.
4. `sc_sales` - Ventas.
5. `sc_cash` - Cajas.
6. `sc_purchases` - Compras.
7. `sc_customer_attention` - Atención al cliente.
8. `sc_customers` - Gestión de clientes.
9. `sc_staff` - Gestión de colaboradores.
10. `sc_items` - Catálogo comercial.
11. `sc_inventory` - Inventario.
12. `sc_restaurant_services` - Restaurante y servicios.
13. `sc_infrastructure` - Infraestructura.
14. `sc_configuration` - Configuración.
15. `sc_reports` - Reportes.

## Orden por empresa

- `sections.order` define el orden funcional base del sistema.
- `companies_sub_sections.section_order` permite ordenar las cabeceras por `company_id` desde BD.
- `companies_sub_sections.sub_section_order` permite ordenar los accesos dentro de cada cabecera por `company_id`.
- El frontend no ordena cabeceras ni accesos. Si una empresa no tiene valores personalizados, `CompanySectionService` usa `sections.order` y `sub_sections.order` como respaldo.
- Perfiles de acceso consume la misma respuesta de `CompanySectionService`, por lo que muestra cabeceras y módulos en el mismo orden que verá el cliente en el menú.

## Subsecciones

1. `home.index` - Inicio / Inicio.
2. `dashboard.index` - Dashboard / Dashboard.
3. `sales.pos` - POS / Venta POS.
4. `restaurant_pos.index` - POS / Restaurante POS.
5. `sales.create` - Ventas / Nuevo.
6. `sales.index` - Ventas / Listado.
7. `quotations.index` - Ventas / Cotizaciones.
8. `cash_registers.registers.index` - Cajas / Cajas.
9. `cash_registers.sessions.index` - Cajas / Aperturas y cierres.
10. `cash_registers.movements.index` - Cajas / Movimientos.
11. `cash_registers.summary.index` - Cajas / Resumen.
12. `misc_expenses.index` - Cajas / Gastos varios.
13. `purchases.new.index` - Compras / Nuevo.
14. `purchases.list.index` - Compras / Listado.
15. `suppliers.index` - Compras / Proveedores.
16. `service_sessions.index` - Atención al cliente / Servicios en curso.
17. `book_complaints.index` - Atención al cliente / Libro de reclamaciones.
18. `tracking_notifications.index` - Atención al cliente / Notificaciones.
19. `tracking_attendances.index` - Atención al cliente / Asistencias por documento.
20. `customers.index` - Gestión de clientes / Clientes.
21. `tracking_customers.index` - Gestión de clientes / Historial.
22. `tracking_subscriptions.index` - Gestión de clientes / Membresías.
23. `users.index` - Gestión de colaboradores / Colaboradores.
24. `user_attendances.index` - Gestión de colaboradores / Asistencia del personal.
25. `products.index` - Catálogo comercial / Productos.
26. `services.index` - Catálogo comercial / Servicios.
27. `subscriptions.index` - Catálogo comercial / Membresías.
28. `categories.index` - Catálogo comercial / Categorías.
29. `brands.index` - Catálogo comercial / Marcas.
30. `recipes.index` - Catálogo comercial / Recetas y platillos.
31. `stocks_management.stock.index` - Inventario / Control de stock.
32. `stocks_management.kardex.index` - Inventario / Kardex.
33. `stocks_management.transfers.index` - Inventario / Traslados.
34. `stocks_management.guides.index` - Inventario / Guías.
35. `stocks_management.valued.index` - Inventario / Kardex valorizado.
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
- `POS` agrupa `Venta POS` y `Restaurante POS` para separar venta rápida de ventas documentadas.
- `Ventas` conserva `Nuevo`, `Listado` y `Cotizaciones`.
- Caja se agrupa en `Cajas`: `Cajas`, `Aperturas y cierres`, `Movimientos`, `Resumen` y `Gastos varios`.
- `Atención al cliente` agrupa `Servicios en curso`, `Libro de reclamaciones`, `Notificaciones` y `Asistencias por documento`.
- `users.index` y `user_attendances.index` se agrupan en `Gestión de colaboradores`.
- Inventario conserva cabecera propia para que perfiles pueda habilitar `Control de stock`, `Kardex`, `Traslados`, `Guías` y `Kardex valorizado` por separado.
- La cabecera visible `Cajas` usa `menu-parent-cash`.
- La cabecera visible `POS` usa `menu-parent-pos`.
- La cabecera visible `Atención al cliente` usa `menu-parent-customer-attention`.
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
