SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `signup_mode` ENUM('signin','signup') NOT NULL DEFAULT 'signup',
  `signup_ip` VARCHAR(45) DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `tier` ENUM('free','builder','pro','enterprise') NOT NULL DEFAULT 'builder',
  `status` ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
  `login_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `otp_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `code_hash` VARCHAR(255) NOT NULL,
  `mode` ENUM('signin','signup') NOT NULL DEFAULT 'signin',
  `status` ENUM('pending','verified','expired','failed','rate_limited') NOT NULL DEFAULT 'pending',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email_status` (`email`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_sessions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `logged_in_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logged_out_at` TIMESTAMP NULL DEFAULT NULL,
  `last_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(191) NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `count` INT UNSIGNED NOT NULL DEFAULT 1,
  `window_start` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_request` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_identifier_action` (`identifier`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
