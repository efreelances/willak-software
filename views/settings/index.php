<?php include BASE_PATH . 'views/layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">Configuración del Sistema</h1>
        <p class="page-subtitle">Administra todos los parámetros de Willak</p>
    </div>
</div>

<?php if ($msg = Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = Session::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <!-- Sidebar de secciones -->
    <div class="col-md-3">
        <div class="card">
            <div class="list-group list-group-flush">
                <a href="<?= APP_URL ?>/settings?section=general" class="list-group-item list-group-item-action <?= $section==='general'?'active':'' ?>">
                    <i class="bi bi-gear me-2"></i> General
                </a>
                <a href="<?= APP_URL ?>/settings?section=security" class="list-group-item list-group-item-action <?= $section==='security'?'active':'' ?>">
                    <i class="bi bi-shield-lock me-2"></i> Seguridad
                </a>
                <a href="<?= APP_URL ?>/settings?section=notifications" class="list-group-item list-group-item-action <?= $section==='notifications'?'active':'' ?>">
                    <i class="bi bi-bell me-2"></i> Notificaciones
                </a>
                <a href="<?= APP_URL ?>/settings?section=tasks" class="list-group-item list-group-item-action <?= $section==='tasks'?'active':'' ?>">
                    <i class="bi bi-check2-square me-2"></i> Tareas
                </a>
                <a href="<?= APP_URL ?>/settings?section=clients" class="list-group-item list-group-item-action <?= $section==='clients'?'active':'' ?>">
                    <i class="bi bi-people me-2"></i> Clientes
                </a>
                <a href="<?= APP_URL ?>/settings?section=reports" class="list-group-item list-group-item-action <?= $section==='reports'?'active':'' ?>">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes
                </a>
                <a href="<?= APP_URL ?>/settings?section=system" class="list-group-item list-group-item-action <?= $section==='system'?'active':'' ?>">
                    <i class="bi bi-hdd me-2"></i> Sistema
                </a>
            </div>
        </div>
    </div>

    <!-- Contenido de la sección -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php
                    $titles = [
                        'general' => '⚙️ Configuración General',
                        'security' => '🔒 Seguridad',
                        'notifications' => '🔔 Notificaciones',
                        'tasks' => '✅ Tareas',
                        'clients' => '👥 Clientes',
                        'reports' => '📊 Reportes',
                        'system' => '🖥️ Sistema'
                    ];
                    echo $titles[$section] ?? 'Configuración';
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/settings/save">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="<?= $section ?>">

                    <?php if ($section === 'general'): ?>
                        <!-- GENERAL -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de la aplicación</label>
                                <input type="text" name="data[app_name]" class="form-control" value="<?= Security::e(!empty($settings['app_name']) ? $settings['app_name'] : 'Willak') ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tema por defecto</label>
                                <select name="data[theme_default]" class="form-select" disabled>
                                    <option value="light" <?= ($settings['theme_default'] ?? 'light')==='light'?'selected':'' ?>>Claro</option>
                                    <option value="dark" <?= ($settings['theme_default'] ?? '')==='dark'?'selected':'' ?>>Oscuro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color primario</label>
                                <input type="color" name="data[primary_color]" id="primaryColor" class="form-control form-control-color" value="<?= Security::e($settings['primary_color'] ?? '#0d47a1') ?>" title="Elige un color" style="width: 100%; padding: 0.375rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color de acento</label>
                                <input type="color" name="data[accent_color]" id="accentColor" class="form-control form-control-color" value="<?= Security::e($settings['accent_color'] ?? '#ff6f00') ?>" title="Elige un color" style="width: 100%; padding: 0.375rem;">
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-outline-secondary flex-grow-1" onclick="resetColors()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Colores por defecto
                                </button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zona horaria</label>
                                <select name="data[timezone]" class="form-select">
                                    <option value="America/Lima" <?= ($settings['timezone'] ?? '')==='America/Lima'?'selected':'' ?>>América/Lima (GMT-5)</option>
                                    <option value="America/Bogota" <?= ($settings['timezone'] ?? '')==='America/Bogota'?'selected':'' ?>>América/Bogotá (GMT-5)</option>
                                    <option value="America/Mexico_City" <?= ($settings['timezone'] ?? '')==='America/Mexico_City'?'selected':'' ?>>América/Ciudad de México (GMT-6)</option>
                                    <option value="America/Argentina/Buenos_Aires" <?= ($settings['timezone'] ?? '')==='America/Argentina/Buenos_Aires'?'selected':'' ?>>América/Buenos Aires (GMT-3)</option>
                                    <option value="Europe/Madrid" <?= ($settings['timezone'] ?? '')==='Europe/Madrid'?'selected':'' ?>>Europa/Madrid (GMT+1)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Formato de fecha</label>
                                <select name="data[date_format]" class="form-select">
                                    <option value="DD/MM/YYYY" <?= ($settings['date_format'] ?? '')==='DD/MM/YYYY'?'selected':'' ?>>DD/MM/YYYY</option>
                                    <option value="MM/DD/YYYY" <?= ($settings['date_format'] ?? '')==='MM/DD/YYYY'?'selected':'' ?>>MM/DD/YYYY</option>
                                    <option value="YYYY-MM-DD" <?= ($settings['date_format'] ?? '')==='YYYY-MM-DD'?'selected':'' ?>>YYYY-MM-DD</option>
                                </select>
                            </div>
                        </div>

                    <?php elseif ($section === 'security'): ?>
                        <!-- SEGURIDAD -->
                        <h6 class="mb-3">Política de Contraseñas</h6>
                        <div class="mb-3">
                            <label class="form-label">Longitud mínima</label>
                            <input type="number" name="data[pwd_min_length]" class="form-control" value="<?= (int)($settings['pwd_min_length'] ?? 8) ?>" min="6" max="20">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[pwd_require_uppercase]" value="1" <?= !empty($settings['pwd_require_uppercase'])?'checked':'' ?>>
                                <label class="form-check-label">Requerir mayúsculas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[pwd_require_number]" value="1" <?= !empty($settings['pwd_require_number'])?'checked':'' ?>>
                                <label class="form-check-label">Requerir números</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[pwd_require_special]" value="1" <?= !empty($settings['pwd_require_special'])?'checked':'' ?>>
                                <label class="form-check-label">Requerir caracteres especiales</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiración de contraseña (días)</label>
                            <input type="number" name="data[pwd_expiry_days]" class="form-control" value="<?= (int)($settings['pwd_expiry_days'] ?? 180) ?>">
                            <small class="text-muted">180 días = 6 meses</small>
                        </div>
                        <hr>
                        <h6 class="mb-3">Autenticación de Dos Factores (2FA)</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="data[2fa_enabled]" value="1" <?= !empty($settings['2fa_enabled'])?'checked':'' ?>>
                                <label class="form-check-label">Habilitar 2FA globalmente</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[2fa_required_admin]" value="1" <?= !empty($settings['2fa_required_admin'])?'checked':'' ?>>
                                <label class="form-check-label">Obligatorio para administradores</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[2fa_required_user]" value="1" <?= !empty($settings['2fa_required_user'])?'checked':'' ?>>
                                <label class="form-check-label">Obligatorio para usuarios</label>
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Sesión</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Timeout de inactividad (minutos)</label>
                                <input type="number" name="data[session_timeout]" class="form-control" value="<?= (int)($settings['session_timeout'] ?? 30) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duración de "Recordarme" (días)</label>
                                <input type="number" name="data[remember_me_days]" class="form-control" value="<?= (int)($settings['remember_me_days'] ?? 7) ?>">
                            </div>
                        </div>

                    <?php elseif ($section === 'notifications'): ?>
                        <!-- NOTIFICACIONES (Versión Detallada) -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Configura los canales de notificación automática para tareas próximas a vencer.
                        </div>

                        <!-- Canal: Email -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Correo Electrónico (SMTP)</h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailActive" <?= ($channels['email']['is_active'] ?? 0) ? 'checked' : '' ?> onchange="toggleChannel('email', this.checked)">
                                    <label class="form-check-label" for="emailActive">Activo</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Servidor SMTP</label>
                                        <input type="text" name="channel_config[email][host]" class="form-control" value="<?= Security::e($channels['email']['config']['host'] ?? '') ?>" placeholder="smtp.tudominio.com">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Puerto</label>
                                        <input type="number" name="channel_config[email][port]" class="form-control" value="<?= Security::e($channels['email']['config']['port'] ?? '587') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Encriptación</label>
                                        <select name="channel_config[email][encryption]" class="form-select">
                                            <option value="tls" <?= ($channels['email']['config']['encryption'] ?? '')==='tls'?'selected':'' ?>>TLS</option>
                                            <option value="ssl" <?= ($channels['email']['config']['encryption'] ?? '')==='ssl'?'selected':'' ?>>SSL</option>
                                            <option value="none" <?= ($channels['email']['config']['encryption'] ?? '')==='none'?'selected':'' ?>>Ninguna</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Usuario / Email</label>
                                        <input type="text" name="channel_config[email][username]" class="form-control" value="<?= Security::e($channels['email']['config']['username'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" name="channel_config[email][password]" class="form-control" value="<?= Security::e($channels['email']['config']['password'] ?? '') ?>" placeholder="Dejar vacío para no cambiar">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email remitente</label>
                                        <input type="email" name="channel_config[email][from_email]" class="form-control" value="<?= Security::e($channels['email']['config']['from_email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre remitente</label>
                                        <input type="text" name="channel_config[email][from_name]" class="form-control" value="<?= Security::e($channels['email']['config']['from_name'] ?? 'Willak') ?>">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="testEmailChannel()">
                                        <i class="bi bi-send me-1"></i> Enviar email de prueba
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Canal: WhatsApp -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp</h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="whatsappActive" <?= ($channels['whatsapp']['is_active'] ?? 0) ? 'checked' : '' ?> onchange="toggleChannel('whatsapp', this.checked)">
                                    <label class="form-check-label" for="whatsappActive">Activo</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Proveedor</label>
                                    <select name="channel_config[whatsapp][provider]" class="form-select" id="waProvider" onchange="toggleWaProvider()">
                                        <option value="callmebot" <?= ($channels['whatsapp']['config']['provider'] ?? '')==='callmebot'?'selected':'' ?>>CallMeBot (Gratis - Pruebas)</option>
                                        <option value="meta" <?= ($channels['whatsapp']['config']['provider'] ?? '')==='meta'?'selected':'' ?>>Meta Cloud API (Oficial)</option>
                                        <option value="twilio" <?= ($channels['whatsapp']['config']['provider'] ?? '')==='twilio'?'selected':'' ?>>Twilio (Pago)</option>
                                    </select>
                                </div>
                                <!-- CallMeBot -->
                                <div id="wa-callmebot" class="row g-3" style="display: <?= ($channels['whatsapp']['config']['provider'] ?? 'callmebot')==='callmebot'?'block':'none' ?>">
                                    <div class="col-md-6">
                                        <label class="form-label">API Key</label>
                                        <input type="text" name="channel_config[whatsapp][api_key]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['api_key'] ?? '') ?>">
                                        <small class="text-muted">Obtén tu API key en <a href="https://www.callmebot.com" target="_blank">callmebot.com</a></small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono (con código país)</label>
                                        <input type="text" name="channel_config[whatsapp][phone]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['phone'] ?? '') ?>" placeholder="+51999999999">
                                    </div>
                                </div>
                                <!-- Meta Cloud API -->
                                <div id="wa-meta" class="row g-3" style="display: <?= ($channels['whatsapp']['config']['provider'] ?? '')==='meta'?'block':'none' ?>">
                                    <div class="col-md-6">
                                        <label class="form-label">Access Token</label>
                                        <input type="password" name="channel_config[whatsapp][meta_token]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['meta_token'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number ID</label>
                                        <input type="text" name="channel_config[whatsapp][meta_phone_id]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['meta_phone_id'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Versión API</label>
                                        <input type="text" name="channel_config[whatsapp][meta_version]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['meta_version'] ?? 'v18.0') ?>">
                                    </div>
                                </div>
                                <!-- Twilio -->
                                <div id="wa-twilio" class="row g-3" style="display: <?= ($channels['whatsapp']['config']['provider'] ?? '')==='twilio'?'block':'none' ?>">
                                    <div class="col-md-6">
                                        <label class="form-label">Account SID</label>
                                        <input type="text" name="channel_config[whatsapp][twilio_sid]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['twilio_sid'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Auth Token</label>
                                        <input type="password" name="channel_config[whatsapp][twilio_token]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['twilio_token'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Número From (WhatsApp)</label>
                                        <input type="text" name="channel_config[whatsapp][twilio_from]" class="form-control" value="<?= Security::e($channels['whatsapp']['config']['twilio_from'] ?? '') ?>" placeholder="whatsapp:+14155238886">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Canal: Telegram -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-telegram me-2 text-info"></i>Telegram Bot</h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="telegramActive" <?= ($channels['telegram']['is_active'] ?? 0) ? 'checked' : '' ?> onchange="toggleChannel('telegram', this.checked)">
                                    <label class="form-check-label" for="telegramActive">Activo</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Bot Token</label>
                                        <input type="password" name="channel_config[telegram][bot_token]" class="form-control" value="<?= Security::e($channels['telegram']['config']['bot_token'] ?? '') ?>" placeholder="123456:ABC-DEF...">
                                        <small class="text-muted">Obtén tu token de <a href="https://t.me/BotFather" target="_blank">@BotFather</a></small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Chat ID</label>
                                        <input type="text" name="channel_config[telegram][chat_id]" class="form-control" value="<?= Security::e($channels['telegram']['config']['chat_id'] ?? '') ?>" placeholder="-100123456789">
                                        <small class="text-muted">Usa <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a> para obtener tu ID</small>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="testTelegramChannel()">
                                        <i class="bi bi-send me-1"></i> Enviar mensaje de prueba
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reglas de notificación -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Reglas de notificación</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Las notificaciones se enviarán automáticamente según estos días de anticipación:</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Primer aviso (días antes)</label>
                                        <input type="number" name="notification_rules[days_1]" class="form-control" value="<?= Security::e($settings['notification_days_1'] ?? '30') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Segundo aviso (días antes)</label>
                                        <input type="number" name="notification_rules[days_2]" class="form-control" value="<?= Security::e($settings['notification_days_2'] ?? '15') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Aviso crítico (días antes)</label>
                                        <input type="number" name="notification_rules[days_3]" class="form-control" value="<?= Security::e($settings['notification_days_3'] ?? '7') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($section === 'tasks'): ?>
                        <!-- TAREAS -->
                        <h6 class="mb-3">Estados permitidos</h6>
                        <div class="mb-3">
                            <?php foreach (['activo','pendiente','en_proceso','cancelado','archivado'] as $s): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="data[task_statuses][]" value="<?= $s ?>" id="status<?= $s ?>" checked disabled>
                                    <label class="form-check-label" for="status<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <h6 class="mb-3">Auto-archivado</h6>
                        <div class="mb-3">
                            <label class="form-label">Días después del vencimiento para archivar automáticamente</label>
                            <input type="number" name="data[task_auto_archive_days]" class="form-control" value="<?= (int)($settings['task_auto_archive_days'] ?? 0) ?>">
                            <small class="text-muted">0 = archivar inmediatamente al vencer</small>
                        </div>
                        <h6 class="mb-3">Campos obligatorios</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[task_require_type]" value="1" <?= !empty($settings['task_require_type'])?'checked':'' ?>>
                                <label class="form-check-label">Tipo de tarea obligatorio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[task_require_description]" value="1" <?= !empty($settings['task_require_description'])?'checked':'' ?>>
                                <label class="form-check-label">Descripción obligatoria</label>
                            </div>
                        </div>

                    <?php elseif ($section === 'clients'): ?>
                        <!-- CLIENTES -->
                        <h6 class="mb-3">Campos obligatorios</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[client_require_document]" value="1" <?= !empty($settings['client_require_document'])?'checked':'' ?>>
                                <label class="form-check-label">Documento obligatorio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[client_require_email]" value="1" <?= !empty($settings['client_require_email'])?'checked':'' ?>>
                                <label class="form-check-label">Email obligatorio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[client_require_phone]" value="1" <?= !empty($settings['client_require_phone'])?'checked':'' ?>>
                                <label class="form-check-label">Teléfono obligatorio</label>
                            </div>
                        </div>
                        <h6 class="mb-3">Validaciones</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[client_validate_email]" value="1" <?= !empty($settings['client_validate_email'])?'checked':'' ?>>
                                <label class="form-check-label">Validar formato de email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[client_unique_document]" value="1" <?= !empty($settings['client_unique_document'])?'checked':'' ?>>
                                <label class="form-check-label">Documento único (no duplicados)</label>
                            </div>
                        </div>

                    <?php elseif ($section === 'reports'): ?>
                        <!-- REPORTES -->
                        <h6 class="mb-3">Formatos de exportación</h6>
                        <div class="mb-3">
                            <?php foreach (['pdf','excel','csv'] as $f): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="data[report_formats][]" value="<?= $f ?>" id="format<?= $f ?>" <?= in_array($f, json_decode($settings['report_formats'] ?? '["pdf","excel","csv"]', true))?'checked':'' ?>>
                                    <label class="form-check-label" for="format<?= $f ?>"><?= strtoupper($f) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Periodo por defecto (días)</label>
                            <input type="number" name="data[report_default_period]" class="form-control" value="<?= (int)($settings['report_default_period'] ?? 30) ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="data[report_include_charts]" value="1" <?= !empty($settings['report_include_charts'])?'checked':'' ?>>
                                <label class="form-check-label">Incluir gráficos en reportes</label>
                            </div>
                        </div>

                    <?php elseif ($section === 'system'): ?>
                        <!-- SISTEMA -->
                        <h6 class="mb-3">Backup</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="data[backup_enabled]" value="1" <?= !empty($settings['backup_enabled'])?'checked':'' ?>>
                                <label class="form-check-label">Habilitar backup automático</label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Frecuencia</label>
                                <select name="data[backup_frequency]" class="form-select">
                                    <option value="daily" <?= ($settings['backup_frequency'] ?? '')==='daily'?'selected':'' ?>>Diario</option>
                                    <option value="weekly" <?= ($settings['backup_frequency'] ?? '')==='weekly'?'selected':'' ?>>Semanal</option>
                                    <option value="monthly" <?= ($settings['backup_frequency'] ?? '')==='monthly'?'selected':'' ?>>Mensual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retención (días)</label>
                                <input type="number" name="data[backup_retention_days]" class="form-control" value="<?= (int)($settings['backup_retention_days'] ?? 30) ?>">
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Logs</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nivel de log</label>
                                <select name="data[log_level]" class="form-select">
                                    <option value="ERROR" <?= ($settings['log_level'] ?? '')==='ERROR'?'selected':'' ?>>Error</option>
                                    <option value="WARNING" <?= ($settings['log_level'] ?? '')==='WARNING'?'selected':'' ?>>Warning</option>
                                    <option value="INFO" <?= ($settings['log_level'] ?? '')==='INFO'?'selected':'' ?>>Info</option>
                                    <option value="DEBUG" <?= ($settings['log_level'] ?? '')==='DEBUG'?'selected':'' ?>>Debug</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retención de logs (días)</label>
                                <input type="number" name="data[log_retention_days]" class="form-control" value="<?= (int)($settings['log_retention_days'] ?? 90) ?>">
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Mantenimiento</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="data[maintenance_mode]" value="1" <?= !empty($settings['maintenance_mode'])?'checked':'' ?>>
                                <label class="form-check-label">Activar modo mantenimiento</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje de mantenimiento</label>
                            <textarea name="data[maintenance_message]" class="form-control" rows="3"><?= Security::e($settings['maintenance_message'] ?? 'Sistema en mantenimiento. Vuelva pronto.') ?></textarea>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Guardar configuración
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS CONSOLIDADOS -->
<script>
// Funciones de reset de colores
function resetColors() {
    fetch('<?= APP_URL ?>/settings/reset-colors', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= Security::csrfToken() ?>'
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('primaryColor').value = '#0d47a1';
            document.getElementById('accentColor').value = '#ff6f00';
            location.reload(); 
        } else {
            alert('Error al restablecer: ' + (data.message || 'Desconocido'));
        }
    }).catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error de conexión.');
    });
}

// Funciones específicas de la sección de Notificaciones
function toggleChannel(channel, isActive) {
    fetch('<?= APP_URL ?>/settings/toggle-channel', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= Security::csrfToken() ?>&channel=' + channel + '&active=' + (isActive ? 1 : 0)
    }).then(r => r.json()).then(data => {
        if (data.success) {
            alert('Canal ' + (isActive ? 'activado' : 'desactivado') + ' correctamente');
        }
    });
}

function toggleWaProvider() {
    const provider = document.getElementById('waProvider').value;
    document.getElementById('wa-callmebot').style.display = provider === 'callmebot' ? 'block' : 'none';
    document.getElementById('wa-meta').style.display = provider === 'meta' ? 'block' : 'none';
    document.getElementById('wa-twilio').style.display = provider === 'twilio' ? 'block' : 'none';
}

// ✅ MODIFICADO: Envío real de email de prueba
function testEmailChannel() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';
    btn.disabled = true;

    fetch('<?= APP_URL ?>/settings/test-email', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= Security::csrfToken() ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error de conexión: ' + error);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// ✅ MODIFICADO: Envío real de mensaje de prueba por Telegram
function testTelegramChannel() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';
    btn.disabled = true;

    fetch('<?= APP_URL ?>/settings/test-telegram', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= Security::csrfToken() ?>'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error de conexión: ' + error);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>