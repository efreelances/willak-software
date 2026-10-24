# Auditoría de configuración de Willak

## Corrección aplicada
- `controllers/settings.php`: los checkboxes desmarcados ahora se guardan como `0`; antes el navegador omitía esos campos y podían quedarse activos en la base de datos.
- En Reportes, desmarcar todos los formatos ahora guarda una lista vacía en lugar de conservar los formatos previos.
- El manejo de errores revierte la transacción cuando corresponde y registra el detalle en el log del servidor sin mostrar SQL/credenciales al usuario.

## Resultado de revisión estática (no es una prueba funcional de producción)

| Sección | Estado en el código revisado |
|---|---|
| General | Los parámetros editables se guardan en `settings`. `app_name` y `theme_default` están deshabilitados en la vista, por lo que no se guardan desde ese formulario. El uso efectivo de zona horaria/formato/colores debe comprobarse en cada módulo. |
| Seguridad | Los parámetros se guardan; no se encontró aplicación general de `pwd_min_length`, requisitos de contraseña, expiración, 2FA ni `session_timeout` en la lógica de autenticación/sesión. No deben considerarse funciones activas solo por aparecer en la pantalla. |
| Notificaciones | Se guardan configuración de canales y reglas. El motor de notificaciones corregido trabaja con `notification_queue`; el código revisado no consume `notification_days_1/2/3` para generar la cola. Debe conectarse esa regla con la programación real antes de afirmar que modifica los avisos enviados. |
| Tareas | Se guardan `task_auto_archive_days`, `task_require_type` y `task_require_description`; no se encontró su aplicación en el modelo/controlador de tareas. Los estados aparecen marcados como deshabilitados en la vista. |
| Clientes | Se guardan las preferencias de campos obligatorios y validación, pero no se encontró aplicación de estas claves en el controlador/modelo de clientes. |
| Reportes | Se guardan formatos y periodo; no se encontró uso de `report_formats`, `report_default_period` ni `report_include_charts` en los controladores de reportes. |
| Sistema | Se guardan parámetros de backup, logs y mantenimiento, pero no se encontró implementación que ejecute backups automáticos, retención de logs o modo mantenimiento. |

## Pendiente para considerarlo terminado
1. Implementar las opciones de Seguridad en validación de contraseñas y gestión de sesión; no activar 2FA hasta implementar el flujo completo.
2. Conectar las reglas de notificación a la programación de recordatorios.
3. Hacer que Tareas y Clientes apliquen los requisitos guardados en servidor (no solo en la interfaz).
4. Restringir las opciones de exportación de Reportes según formatos habilitados.
5. Implementar un proceso real de backup con ruta segura, rotación y restauración probada; implementar mantenimiento con protección de acceso administrativo.
6. Probar cada sección con la base de datos local y revisar los logs de Laragon.

Esta auditoría se basa en lectura estática del código fuente incluido en la copia revisada. No se ha conectado al Laragon del usuario ni se han realizado pruebas con su base de datos.
