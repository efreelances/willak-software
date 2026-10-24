-- ============================================
-- WILLAK - Sistema de Gestión de Tareas
-- Base de datos completa
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `willak` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `willak`;

-- ============================================
-- TABLA: users
-- ============================================
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `password_changed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `totp_secret` VARCHAR(32) DEFAULT NULL,
  `totp_enabled` TINYINT(1) DEFAULT 0,
  `status` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `remember_token` VARCHAR(255) DEFAULT NULL,
  `remember_expires` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: task_types (tipos de tareas)
-- ============================================
CREATE TABLE `task_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: clients (clientes - personas/empresas)
-- ============================================
CREATE TABLE `clients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `document` VARCHAR(20) DEFAULT NULL,
  `document_type` ENUM('DNI', 'RUC', 'PASAPORTE', 'OTRO') DEFAULT 'DNI',
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: tasks (tareas con vigencia)
-- ============================================
CREATE TABLE `tasks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `client_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `task_type_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `reminder_days` JSON DEFAULT NULL,
  `status` ENUM('activo', 'pendiente', 'en_proceso', 'cancelado', 'archivado') NOT NULL DEFAULT 'pendiente',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`task_type_id`) REFERENCES `task_types`(`id`) ON DELETE SET NULL,
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notification_settings (config por usuario)
-- ============================================
CREATE TABLE `notification_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `whatsapp_enabled` TINYINT(1) DEFAULT 0,
  `whatsapp_phone` VARCHAR(20) DEFAULT NULL,
  `whatsapp_provider` ENUM('meta', 'twilio', 'callmebot') DEFAULT 'meta',
  `telegram_enabled` TINYINT(1) DEFAULT 0,
  `telegram_bot_token` VARCHAR(100) DEFAULT NULL,
  `telegram_chat_id` VARCHAR(50) DEFAULT NULL,
  `email_enabled` TINYINT(1) DEFAULT 0,
  `email_address` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notification_queue (scheduler)
-- ============================================
CREATE TABLE `notification_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `channel` ENUM('whatsapp', 'telegram', 'email') NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `attempts` INT(11) DEFAULT 0,
  `max_attempts` INT(11) DEFAULT 3,
  `status` ENUM('pending', 'sent', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notification_log (historial)
-- ============================================
CREATE TABLE `notification_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `channel` ENUM('whatsapp', 'telegram', 'email') NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('success', 'failed') NOT NULL,
  `error_message` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: settings (configuración global)
-- ============================================
CREATE TABLE `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Usuario admin inicial (password: Admin@123)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@willak.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2u