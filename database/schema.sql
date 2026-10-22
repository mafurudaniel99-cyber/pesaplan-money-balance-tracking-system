-- =====================================================================
-- PesaPlan — Mfumo wa Nidhamu ya Fedha
-- MySQL Database Schema
-- ---------------------------------------------------------------------
-- Engine: InnoDB (FK + transactional safety)
-- Charset: utf8mb4 (full Kiswahili / emoji support)
-- Currency: TZS stored as DECIMAL(15,2) — never FLOAT
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET time_zone = '+03:00';

-- =====================================================================
-- 1. users
-- =====================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(120) NOT NULL,
  `email`           VARCHAR(190) NOT NULL,
  `password_hash`   VARCHAR(255) NOT NULL,
  `phone`           VARCHAR(30)  DEFAULT NULL,
  `preferred_language` VARCHAR(10) NOT NULL DEFAULT 'sw',
  `timezone`        VARCHAR(60)  NOT NULL DEFAULT 'Africa/Dar_es_Salaam',
  `currency`        VARCHAR(5)   NOT NULL DEFAULT 'TZS',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `failed_logins`   INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`    DATETIME     DEFAULT NULL,
  `last_login_at`   DATETIME     DEFAULT NULL,
  `onboarding_complete` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. user_settings
-- =====================================================================
CREATE TABLE IF NOT EXISTS `user_settings` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`                INT UNSIGNED NOT NULL,
  `expected_salary`        DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `expected_salary_day`    TINYINT UNSIGNED NOT NULL DEFAULT 28
                          COMMENT 'Day of month salary usually arrives (1-28)',
  `cooling_off_hours`      INT UNSIGNED NOT NULL DEFAULT 24
                          COMMENT 'Discretionary purchase cooling-off window',
  `notify_budget_threshold` TINYINT(1) NOT NULL DEFAULT 1,
  `notify_upcoming_bills`  TINYINT(1) NOT NULL DEFAULT 1,
  `notify_review_reminders` TINYINT(1) NOT NULL DEFAULT 1,
  `budget_threshold_pct`   TINYINT UNSIGNED NOT NULL DEFAULT 80,
  `created_at`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_settings_user` (`user_id`),
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. expense_categories
-- Holds both income-side and expense-side categories (type column).
-- =====================================================================
CREATE TABLE IF NOT EXISTS `expense_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL
                COMMENT 'NULL means system default category shared by all',
  `name`        VARCHAR(80)  NOT NULL,
  `type`        ENUM('income','expense','savings','debt') NOT NULL DEFAULT 'expense',
  `icon`        VARCHAR(50)  NOT NULL DEFAULT 'fa-tag',
  `color`       VARCHAR(20)  NOT NULL DEFAULT '#4a6fa5',
  `kind`        ENUM('essential','discretionary','obligation','savings','other') NOT NULL DEFAULT 'other',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cat_user` (`user_id`),
  KEY `idx_cat_type` (`type`),
  CONSTRAINT `fk_cat_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. income_records
-- Distinguishes EXPECTED salary from RECEIVED salary. A salary row may
-- exist as expected for a given month and later be flagged received.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `income_records` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `source`          VARCHAR(120) NOT NULL
                    COMMENT 'e.g. Mshahara, Biashara, Bonus',
  `expected_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `received_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `expected_date`   DATE NOT NULL,
  `received_date`   DATE DEFAULT NULL,
  `is_received`     TINYINT(1)   NOT NULL DEFAULT 0,
  `is_salary`       TINYINT(1)   NOT NULL DEFAULT 0,
  `period_month`    CHAR(7)      NOT NULL
                    COMMENT 'YYYY-MM bucket for the income',
  `notes`           VARCHAR(255) DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inc_user` (`user_id`),
  KEY `idx_inc_month` (`user_id`,`period_month`),
  CONSTRAINT `fk_inc_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. transactions
-- Single source of truth for every money movement. `type` controls
-- how it contributes to calculations.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `category_id`    INT UNSIGNED DEFAULT NULL,
  `income_id`      INT UNSIGNED DEFAULT NULL
                   COMMENT 'Link to income_records if this is a salary/other income receipt',
  `type`           ENUM('salary','other_income','expense','savings_in','savings_out','debt_repay','refund','transfer_in','transfer_out','adjustment') NOT NULL,
  `amount`         DECIMAL(15,2) NOT NULL,
  `transaction_date` DATE NOT NULL,
  `description`    VARCHAR(255) NOT NULL,
  `payment_method` ENUM('cash','bank','mobile_money','card','cheque','other') NOT NULL DEFAULT 'cash',
  `notes`          VARCHAR(500) DEFAULT NULL,
  `is_deleted`     TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tx_user_date` (`user_id`,`transaction_date`),
  KEY `idx_tx_user_type` (`user_id`,`type`),
  KEY `idx_tx_category` (`category_id`),
  CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`)
    REFERENCES `expense_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tx_income` FOREIGN KEY (`income_id`)
    REFERENCES `income_records`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. transaction_audit
-- Append-only audit trail of every create / edit / soft-delete so that
-- financial records can always be reconstructed.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `transaction_audit` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `transaction_id` INT UNSIGNED NOT NULL,
  `action`         ENUM('create','update','delete','restore') NOT NULL,
  `snapshot`       JSON DEFAULT NULL,
  `changed_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_tx` (`transaction_id`),
  KEY `idx_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_audit_tx` FOREIGN KEY (`transaction_id`)
    REFERENCES `transactions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. budgets
-- A budget is per user, per month (YYYY-MM).
-- =====================================================================
CREATE TABLE IF NOT EXISTS `budgets` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL,
  `period_month`  CHAR(7) NOT NULL,
  `total_income_planned` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `notes`         VARCHAR(255) DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_budget_user_month` (`user_id`,`period_month`),
  CONSTRAINT `fk_budget_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. budget_items
-- Per-category planned amounts inside a budget.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `budget_items` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `budget_id`      INT UNSIGNED NOT NULL,
  `category_id`    INT UNSIGNED NOT NULL,
  `planned_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `priority`       TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `is_essential`   TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bitem_budget_cat` (`budget_id`,`category_id`),
  KEY `idx_bitem_cat` (`category_id`),
  CONSTRAINT `fk_bitem_budget` FOREIGN KEY (`budget_id`)
    REFERENCES `budgets`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bitem_cat` FOREIGN KEY (`category_id`)
    REFERENCES `expense_categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 9. money_allocations
-- Logical allocation of received salary into purposes (essential
-- expenses, savings, debt, discretionary). Explicitly NOT bank
-- transfers — interface makes this clear.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `money_allocations` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `income_id`      INT UNSIGNED DEFAULT NULL,
  `category_id`    INT UNSIGNED DEFAULT NULL,
  `purpose`        ENUM('essential','rent_bills','debt','emergency_savings','other_savings','development','discretionary','other') NOT NULL,
  `allocated_amount` DECIMAL(15,2) NOT NULL,
  `allocation_date` DATE NOT NULL,
  `notes`          VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alloc_user` (`user_id`),
  KEY `idx_alloc_income` (`income_id`),
  CONSTRAINT `fk_alloc_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_alloc_income` FOREIGN KEY (`income_id`)
    REFERENCES `income_records`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_alloc_cat` FOREIGN KEY (`category_id`)
    REFERENCES `expense_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 10. savings_goals
-- =====================================================================
CREATE TABLE IF NOT EXISTS `savings_goals` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `name`           VARCHAR(120) NOT NULL,
  `target_amount`  DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `current_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `target_date`    DATE DEFAULT NULL,
  `priority`       TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `is_emergency_fund` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `notes`          VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sg_user` (`user_id`),
  CONSTRAINT `fk_sg_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 11. savings_contributions
-- Tied to transactions so savings_in transactions update goal balances
-- consistently. Withdrawals are savings_out and decrement.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `savings_contributions` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `goal_id`       INT UNSIGNED NOT NULL,
  `transaction_id` INT UNSIGNED DEFAULT NULL,
  `amount`        DECIMAL(15,2) NOT NULL,
  `direction`     ENUM('in','out') NOT NULL DEFAULT 'in',
  `contribution_date` DATE NOT NULL,
  `notes`         VARCHAR(255) DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sc_goal` (`goal_id`),
  CONSTRAINT `fk_sc_goal` FOREIGN KEY (`goal_id`)
    REFERENCES `savings_goals`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sc_tx` FOREIGN KEY (`transaction_id`)
    REFERENCES `transactions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 12. debts
-- =====================================================================
CREATE TABLE IF NOT EXISTS `debts` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`             INT UNSIGNED NOT NULL,
  `name`                VARCHAR(120) NOT NULL,
  `original_amount`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `outstanding_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `minimum_payment`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `interest_rate_pct`   DECIMAL(6,3)  NOT NULL DEFAULT 0.000,
  `due_day`             TINYINT UNSIGNED DEFAULT NULL,
  `next_due_date`       DATE DEFAULT NULL,
  `is_recurring`        TINYINT(1) NOT NULL DEFAULT 0,
  `is_paid_off`         TINYINT(1) NOT NULL DEFAULT 0,
  `notes`               VARCHAR(255) DEFAULT NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_debt_user` (`user_id`),
  CONSTRAINT `fk_debt_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 13. debt_payments
-- =====================================================================
CREATE TABLE IF NOT EXISTS `debt_payments` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `debt_id`        INT UNSIGNED NOT NULL,
  `transaction_id` INT UNSIGNED DEFAULT NULL,
  `amount`         DECIMAL(15,2) NOT NULL,
  `interest_portion` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `principal_portion` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `payment_date`   DATE NOT NULL,
  `notes`          VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dp_debt` (`debt_id`),
  CONSTRAINT `fk_dp_debt` FOREIGN KEY (`debt_id`)
    REFERENCES `debts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dp_tx` FOREIGN KEY (`transaction_id`)
    REFERENCES `transactions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 14. financial_obligations
-- Recurring bills (rent, utilities) and other upcoming commitments
-- that are NOT debt principal — distinct from debts table.
-- =====================================================================
CREATE TABLE IF NOT EXISTS `financial_obligations` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `name`           VARCHAR(120) NOT NULL,
  `amount`         DECIMAL(15,2) NOT NULL,
  `due_day`        TINYINT UNSIGNED DEFAULT NULL,
  `next_due_date`  DATE DEFAULT NULL,
  `is_recurring`   TINYINT(1) NOT NULL DEFAULT 1,
  `is_paid`        TINYINT(1) NOT NULL DEFAULT 0,
  `category_id`    INT UNSIGNED DEFAULT NULL,
  `notes`          VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_obl_user` (`user_id`),
  CONSTRAINT `fk_obl_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_obl_cat` FOREIGN KEY (`category_id`)
    REFERENCES `expense_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 15. planned_purchases  ("Kagua Kabla ya Kutumia")
-- =====================================================================
CREATE TABLE IF NOT EXISTS `planned_purchases` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           INT UNSIGNED NOT NULL,
  `item_name`         VARCHAR(180) NOT NULL,
  `proposed_cost`     DECIMAL(15,2) NOT NULL,
  `reason`            VARCHAR(255) NOT NULL,
  `category_id`       INT UNSIGNED DEFAULT NULL,
  `classification`    ENUM('essential','useful','discretionary') NOT NULL DEFAULT 'discretionary',
  `can_postpone`      TINYINT(1) NOT NULL DEFAULT 1,
  `proposed_date`     DATE NOT NULL,
  `status`            ENUM('pending','cooling','approved','rejected','completed') NOT NULL DEFAULT 'cooling',
  `cooling_until`     DATETIME DEFAULT NULL,
  `decision_result`   VARCHAR(120) DEFAULT NULL,
  `decision_explanation` TEXT DEFAULT NULL,
  `completed_at`      DATETIME DEFAULT NULL,
  `notes`             VARCHAR(255) DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pp_user` (`user_id`),
  KEY `idx_pp_status` (`status`),
  CONSTRAINT `fk_pp_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pp_cat` FOREIGN KEY (`category_id`)
    REFERENCES `expense_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 16. financial_reviews
-- =====================================================================
CREATE TABLE IF NOT EXISTS `financial_reviews` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL,
  `review_type`   ENUM('daily','weekly','monthly') NOT NULL,
  `review_date`   DATE NOT NULL,
  `period_label`  VARCHAR(40) NOT NULL,
  `checklist`     JSON NOT NULL,
  `notes`         VARCHAR(500) DEFAULT NULL,
  `is_completed`  TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at`  DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_user_type_period` (`user_id`,`review_type`,`period_label`),
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 17. notifications
-- =====================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `type`        VARCHAR(40) NOT NULL,
  `title`       VARCHAR(180) NOT NULL,
  `body`        VARCHAR(500) DEFAULT NULL,
  `icon`        VARCHAR(50)  NOT NULL DEFAULT 'fa-bell',
  `severity`    ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `action_url`  VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 18. audit_logs
-- Generic audit log for important non-transaction changes
-- (login, settings change, account deletion, exports).
-- =====================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `action`     VARCHAR(80) NOT NULL,
  `entity`     VARCHAR(80) DEFAULT NULL,
  `entity_id`  VARCHAR(80) DEFAULT NULL,
  `details`    JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auditlog_user` (`user_id`),
  KEY `idx_auditlog_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
