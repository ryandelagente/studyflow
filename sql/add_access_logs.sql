-- Migration: Create access_logs table
-- Run in phpMyAdmin on the productivity_hub database

CREATE TABLE IF NOT EXISTS `access_logs` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT NOT NULL,
    `tenant_id`  INT NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
    `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
    `action`     VARCHAR(64) NOT NULL DEFAULT 'login',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user`   (`user_id`),
    INDEX `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
