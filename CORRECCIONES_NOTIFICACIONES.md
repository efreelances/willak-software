# Willak — copia corregida de notificaciones

Esta carpeta es una copia de trabajo independiente. No modifica el repositorio original ni se conecta automáticamente a producción.

## Correcciones incluidas

- Se unificó el motor con el esquema de `willak.sql`: `notification_queue`, `notification_log`, `notification_settings` y `settings`.
- El cron procesa únicamente registros pendientes cuya `scheduled_at` ya venció.
- Los envíos exitosos se marcan como `sent` y se registran en `notification_log`.
- Los errores quedan registrados y se reintentan con espera progresiva; al alcanzar `max_attempts`, quedan en `failed`.
- Se añadió bloqueo MySQL con `GET_LOCK` para evitar dos ejecuciones simultáneas del cron.
- Se cancelan recordatorios pendientes al archivar o cancelar tareas, y se reprograman al reactivar.
- Se eliminaron referencias activas a tablas que no existen en el SQL entregado (`app_settings`, `notification_channels`, `user_notification_prefs`, `notifications`).
- La configuración de aplicación de esta copia oculta errores al visitante por defecto. Las credenciales se deben establecer en variables de entorno.
- El cron solo acepta ejecución por CLI y escribe su registro en el directorio temporal del sistema.

## Requisitos y configuración

1. PHP con PDO MySQL y cURL; PHPMailer se incluye mediante Composer en `vendor/`.
2. Crear una base de datos MySQL/MariaDB y aplicar el archivo SQL original `willak.sql`.
3. Configurar estas variables de entorno en el servidor:
   - `WILLAK_DB_HOST`
   - `WILLAK_DB_NAME`
   - `WILLAK_DB_USER`
   - `WILLAK_DB_PASS`
   - Opcional: `WILLAK_DEBUG=1` solo en desarrollo.
4. Usar un usuario de base de datos dedicado, con permisos mínimos sobre la base `willak`; no usar `root` ni una contraseña vacía.
5. En Configuración de Willak, guardar los parámetros SMTP o del proveedor de WhatsApp. Los canales globales se controlan con las claves `notification_email_active`, `notification_whatsapp_active` y `notification_telegram_active`.
6. Cada usuario que reciba avisos necesita una fila en `notification_settings` con el canal habilitado y su destinatario. Para Telegram también se necesita `telegram_bot_token` y `telegram_chat_id`. La tarea debe tener `reminder_days` con los días de anticipación deseados.
7. Programar el cron cada 5 minutos, por ejemplo:
   `php /ruta/absoluta/willak/cron/check_tasks.php`

## Pruebas realizadas en esta copia

- Validación sintáctica con `php -l`: 130 archivos PHP revisados, sin errores de sintaxis.
- No se ejecutaron envíos reales ni pruebas de integración contra MySQL: este entorno no dispone de la extensión PDO MySQL ni de credenciales de una base de datos de prueba.
- No se ejecutó ninguna consulta ni cambio sobre el servidor de producción.

## Antes de producción

- Cambiar la contraseña del usuario administrador inicial y revisar el hash de credenciales de ejemplo incluido en el SQL.
- Confirmar que las credenciales SMTP/WhatsApp/Telegram están configuradas y que las pruebas se envían a destinatarios controlados.
- Probar primero con una tarea ficticia y un único destinatario.
- Revisar que los permisos de `config/` impidan su lectura pública y mantener los logs fuera del directorio público.


## Segunda revisión: sesión y cierre de sesión

- Corregido `core/Router.php`: la ruta `/logout` ahora llama a `AuthController::actionLogout()` en vez de caer en `actionIndex()`. Antes, `actionIndex()` detectaba la sesión autenticada y redirigía de vuelta al dashboard; este es el motivo del fallo descrito, no una simple caché del navegador.
- Corregido el cálculo de `session.cookie_secure` para no tratar `HTTPS=off` como conexión segura.
- Corregido el cálculo del esquema de `APP_URL` para aceptar valores habituales de `HTTPS` distintos de `on`.
- Retirados del controlador los mensajes temporales de depuración que registraban el ID de sesión y el ID de usuario.
- `test_notifications.php` ahora solo funciona desde CLI y consulta el estado de la cola sin enviar mensajes.

## Validación de esta revisión

- Se ejecutó `php -l` sobre los archivos PHP de la copia y no se detectaron errores de sintaxis.
- No se ejecutaron pruebas de integración contra MySQL ni envíos reales; hace falta una base de datos de prueba y credenciales de proveedores.
- Antes de producción: probar login, logout, navegación con sesión cerrada, expiración por inactividad, opción «recordarme», y un envío controlado por canal.
