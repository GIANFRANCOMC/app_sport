# 01 - Inicio

## Que hace

Pantalla inicial interna. Sirve como entrada al sistema y para cargar preferencias/configuracion visual de secciones disponibles para el usuario o empresa.

## Archivos

- Ruta: `routes/System/Essentials/Home.php`
- Controlador: `HomeController`
- Vista: `resources/views/System/general/Essentials/home/main.blade.php`
- Vue: `resources/js/System/Pages/Essentials/home`
- Tablas: `users`, `user_preferences`, `companies_sub_sections`, `sections`, `sub_sections`

## Reglas

- Requiere usuario autenticado.
- La configuracion debe respetar empresa y usuario.
- No debe mostrar subsecciones no habilitadas para la empresa.

## Campos relevantes

- `user_preferences.slug`
- `user_preferences.value`
- `companies_sub_sections.status`
- `sub_sections.dom_route`

## Mejoras sugeridas

- Documentar exactamente que preferencias se guardan.
- Validar que las subsecciones actualizadas pertenezcan a la empresa.
- Evitar guardar configuraciones duplicadas por usuario.

