# 00 - Orden de modulos System

Este archivo es el indice funcional y contrato de navegacion de System. Refleja el orden definido para el menu, relaciona cada acceso con su ruta y permite documentar modulos aunque todavia no esten activos para una empresa.

## Para que sirve

- Mantener un orden estable entre menu, documentacion y modulos.
- Identificar que rutas pertenecen a cada seccion.
- Diferenciar modulos planificados, habilitados e implementados.
- Evitar que un cambio de UI altere sin querer la estructura funcional.
- Servir como punto de entrada antes de revisar el archivo detallado de cada modulo.

Este archivo no controla el menu en ejecucion. El menu real se obtiene de `sections`, `sub_sections` y `companies_sub_sections` mediante `CompanySectionService`. El layout consume el servicio y no conoce la clave de caché. Si cambia el orden funcional o se agrega un modulo, deben actualizarse tanto los datos del sistema como este indice.

## Secciones principales

1. `sc_home` - Inicio, define literalmente el home donde se listan todos los modulos para poder acceder
2. `sc_dashboard` - Dashboard, define el panel de control que muestra detalles basicos como contadores y una grafica de ventas
3. `sc_sales` - Ventas, define la seccion donde se realizaran las ventas, tanto el listado como agregar venta
4. `sc_customers` - Gestion de clientes, define seccion donde se gestionaran los clientes y lo asociados a estos
5. `sc_items` - Catalogo comercial, define la seccion donde se gestionara lo que se vendera y administrara en el stock (inventario)
6. `sc_infrastructure` - Infraestructura, define todo lo relacionado a la sucursal (empresa), todo lo perteneciente a este
7. `sc_configuration` - Configuracion, define las maestras y temas de configuracion para el sistema
8. `sc_reports` - Reportes, define los reportes de las secciones para poder exportar

## Subsecciones

1. `home.index` - Inicio, es el home donde van los apartados, menu literalmente
2. `dashboard.index` - Dashboard, panel de control donde se muestran detalle basicos y graficas
3. `sales.index` - Ventas / Listado, donde se listan las ventas, anulan las ventas, imprimen las ventas
4. `sales.create` - Ventas / Nuevo, donde se crean las ventas, imprimen despues de la creacion
5. `customers.index` - Clientes, donde se gestionan los clientes, agregar, modificar
6. `tracking_customers.index` - Historial de cliente, donde se visualiza las diferentes actividades y registros del usuario, ventas, asistencias, membresias, etc
7. `tracking_subscriptions.index` - Membresias de clientes, listar de membresias creadas desde la venta
8. `tracking_attendances.index` - Asistencias, donde se registran las asistencias, ya sea manual, por qr, o por lector biometrico, este ultimo falta
9. `tracking_notifications.index` - Notificaciones, donde se visualizan el listado de notificaciones pendientes y enviadas
10. `book_complaints.index` - Libro de reclamaciones y sugerencias, donde se gestiona la informacion de libro de reclamaciones expuestos para el cliente
11. `products.index` - Productos, gestion de productos para vender y es contabilizado en para el stock x almacen
12. `services.index` - Servicios, gestion de servicios para vender y no es contabilizado en para el stock x almacen
13. `subscriptions.index` - Membresías de catálogo, gestión de membresías para vender y varía el periodo
14. `categories.index` - Categorias, estas categorias ayudan a la clasificacion de productos, servicios y membresias para su mejor agrupacion y busqueda
15. `brands.index` - Marcas, catálogo propio de cada empresa para identificar y agrupar productos
16. `stocks_management.index` - Inventario: consulta existencias por almacén, registra entradas, salidas y correcciones manuales, y muestra el kardex completo.
17. `branches.index` - Sucursales, gestion de sucursales por empresa, la cual puede tener x sucursales, junto a sus series comprobantes de venta, almacenes, etc
18. `assets.index` - Activos, formulario para agregar, modificar activos para las sucursales
19. `assets_management.index` - Gestion de activos, gestion de activos, asignacion de activo a usuarios, asignacion de activo a sucursales, informacion por unidad solamente por el momento, es decir depende del management_type solamente se maneja que se trata por unidad, tambien hay que se gestione por stock como un todo pero sera para otro momento
20. `biometric_devices.index` - Dispositivos biometricos, registro de dispositivos biometricos que seran usados para registrar asistencias
21. `companies.index` - Mi empresa, gestion de informacion principal y esencial de la empresa
22. `users.index` - Colaboradores, gestion de usuario para tener acceso a la plataforma
23. `reports.index` - Reportes, gestion de reportes filtrando por campos

## Modulos tecnicos de soporte

Ademas del menu visible, System usa entidades tecnicas que tambien deben tratarse como modulos/documentacion:

- Tipos de documento de identidad.
- Tipos de documento comercial.
- Monedas.
- Secciones y subsecciones.
- Roles.
- Preferencias de usuario.
- Series.
- Almacenes.
- Emails de suscripcion.
