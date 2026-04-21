-- ============================================================
-- Migration: Super Admin role + Membership Plans
-- Run this in phpMyAdmin or MySQL CLI against `productivity_hub`
-- ============================================================

-- 1. Ensure super_admin exists in users.role ENUM
ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user';

-- 2. Normalise tenants.plan to VARCHAR so we can use any slug
ALTER TABLE `tenants`
  MODIFY COLUMN `plan`   VARCHAR(50) NOT NULL DEFAULT 'free',
  MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'active';

-- 3. Default any tenants without a plan to 'free'
UPDATE `tenants` SET `plan` = 'free', `status` = 'active'
  WHERE `plan` IS NULL OR `plan` = '';

-- 4. Membership plans reference table
CREATE TABLE IF NOT EXISTS `membership_plans` (
  `id`            int(11)       NOT NULL AUTO_INCREMENT,
  `slug`          varchar(50)   NOT NULL,
  `name`          varchar(100)  NOT NULL,
  `price_monthly` decimal(8,2)  NOT NULL DEFAULT 0.00,
  `price_annual`  decimal(8,2)  NOT NULL DEFAULT 0.00,
  `max_users`     int(11)       NOT NULL DEFAULT 1,   -- -1 = unlimited
  `storage_gb`    decimal(5,1)  NOT NULL DEFAULT 0.5,
  `ai_access`     tinyint(1)    NOT NULL DEFAULT 0,   -- 1 = full AI, 0 = none
  `ai_limited`    tinyint(1)    NOT NULL DEFAULT 1,   -- 1 = limited AI only
  `support_level` varchar(100)  NOT NULL DEFAULT 'Community',
  `badge_color`   varchar(50)   NOT NULL DEFAULT 'gray',
  `is_active`     tinyint(1)    NOT NULL DEFAULT 1,
  `sort_order`    int(11)       NOT NULL DEFAULT 0,
  `created_at`    timestamp     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Seed plan definitions (safe to re-run — uses ON DUPLICATE KEY)
INSERT INTO `membership_plans`
  (`slug`, `name`, `price_monthly`, `price_annual`, `max_users`, `storage_gb`, `ai_access`, `ai_limited`, `support_level`, `badge_color`, `sort_order`)
VALUES
  ('free',     'Free',     0.00,   0.00,   1,  0.5, 0, 0, 'Community',        'gray',   1),
  ('basic',    'Basic',    4.99,  49.99,   1,  1.0, 1, 1, 'Email Support',    'blue',   2),
  ('standard', 'Standard', 9.99,  99.99,   5,  5.0, 1, 0, 'Priority Support', 'purple', 3),
  ('premium',  'Premium', 19.99, 199.99,  -1, 10.0, 1, 0, 'Dedicated Support','gold',   4)
ON DUPLICATE KEY UPDATE
  `price_monthly` = VALUES(`price_monthly`),
  `price_annual`  = VALUES(`price_annual`),
  `max_users`     = VALUES(`max_users`),
  `storage_gb`    = VALUES(`storage_gb`),
  `ai_access`     = VALUES(`ai_access`),
  `ai_limited`    = VALUES(`ai_limited`),
  `support_level` = VALUES(`support_level`),
  `badge_color`   = VALUES(`badge_color`),
  `sort_order`    = VALUES(`sort_order`);
