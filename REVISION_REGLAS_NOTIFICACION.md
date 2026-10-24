# Corrección: guardar reglas de notificación

## Causa identificada
La vista enviaba los campos con claves `days_1`, `days_2`, `days_3`, mientras que al volver a cargar la pantalla buscaba `notification_days_1`, `notification_days_2`, `notification_days_3`. El controlador guardaba las claves recibidas literalmente, por lo que los valores no reaparecían en los campos y parecía que no se guardaban.

## Cambios
- Los campos del formulario ahora envían `notification_days_1`, `notification_days_2` y `notification_days_3`.
- El controlador solo acepta esas tres claves para reglas de notificación.
- Se valida que cada valor sea un entero entre 1 y 365.
- Se guardan en la tabla `settings` con `INSERT ... ON DUPLICATE KEY UPDATE`, dentro de la transacción de configuración existente.

## Prueba manual recomendada
1. Haz una copia de seguridad de la base de datos.
2. Abre Configuración > Notificaciones.
3. Coloca valores de prueba distintos, por ejemplo 28, 12 y 5.
4. Pulsa Guardar configuración.
5. Confirma que regresa a `?section=notifications` y que aparecen 28, 12 y 5.
6. Recarga el navegador y confirma que persisten.
7. Si hay acceso SQL, comprueba:
   `SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('notification_days_1','notification_days_2','notification_days_3');`

## Alcance
Esta corrección resuelve el guardado y la recuperación de los valores de configuración. No cambia la lógica de programación de la cola de notificaciones; si se desea que estos tres valores gobiernen automáticamente la programación de cada tarea, se debe integrar esa regla de negocio por separado y probarla con la tabla `notification_queue`.
