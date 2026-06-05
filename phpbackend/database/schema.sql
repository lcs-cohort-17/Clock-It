CREATE TABLE `alerts` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) NOT NULL,
  `type` enum('late','no-show') NOT NULL,
  `date` date NOT NULL,
  `message` varchar(500) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_alerts_user_type_date` (`user_id`,`type`,`date`),
  KEY `idx_alerts_user_id` (`user_id`),
  KEY `idx_alerts_type` (`type`),
  KEY `idx_alerts_date` (`date`),
  KEY `idx_alerts_is_read` (`is_read`),
  KEY `idx_alerts_user_date` (`user_id`,`date`),
  KEY `idx_alerts_user_type_date` (`user_id`,`type`,`date`),
  CONSTRAINT `fk_alerts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `attendance` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `type` enum('clock_in','clock_out') NOT NULL,
  `device` varchar(500) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `qr_token_id` char(36) DEFAULT NULL,
  `edited_by` char(36) DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_attendance_user_id` (`user_id`),
  KEY `idx_attendance_timestamp` (`timestamp`),
  KEY `idx_attendance_type` (`type`),
  KEY `idx_attendance_qr_token_id` (`qr_token_id`),
  KEY `idx_attendance_user_timestamp` (`user_id`,`timestamp`),
  KEY `idx_attendance_user_type_timestamp` (`user_id`,`type`,`timestamp`),
  KEY `fk_attendance_edited_by` (`edited_by`),
  CONSTRAINT `fk_attendance_edited_by` FOREIGN KEY (`edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_attendance_qr_token` FOREIGN KEY (`qr_token_id`) REFERENCES `qr_tokens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `attendance_logs` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) NOT NULL,
  `event_type` enum('in','out') NOT NULL,
  `event_time` datetime NOT NULL DEFAULT current_timestamp(),
  `check_in_method` enum('qr','manual') NOT NULL DEFAULT 'qr',
  `sync_status` enum('synced','pending','failed') NOT NULL DEFAULT 'synced',
  `location` varchar(255) NOT NULL DEFAULT 'Main Entrance',
  `device_info` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attendance_logs_event_time` (`event_time`),
  KEY `idx_attendance_logs_event_type` (`event_type`),
  KEY `idx_attendance_logs_sync_status` (`sync_status`),
  KEY `idx_attendance_logs_user_event` (`user_id`,`event_type`,`event_time`),
  CONSTRAINT `fk_attendance_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `google_credentials` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `access_token` varchar(2048) NOT NULL,
  `refresh_token` varchar(2048) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_connected` tinyint(1) NOT NULL DEFAULT 0,
  `connected_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_google_credentials_user_id` (`user_id`),
  UNIQUE KEY `uq_google_credentials_employee_id` (`employee_id`),
  KEY `idx_google_credentials_connected` (`is_connected`),
  CONSTRAINT `fk_google_credentials_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `google_sheets_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE `leave_requests` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) NOT NULL,
  `type` enum('sick','annual','unpaid','other') NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `reason` varchar(1000) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_leave_requests_status` (`status`),
  KEY `idx_leave_requests_user_id` (`user_id`),
  KEY `idx_leave_requests_dates` (`start_date`,`end_date`),
  KEY `idx_leave_requests_user_status` (`user_id`,`status`),
  CONSTRAINT `fk_leave_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_leave_end_after_start` CHECK (`end_date` >= `start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
CREATE TABLE `qr_tokens` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `token` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_qr_tokens_token` (`token`),
  KEY `idx_qr_tokens_expires_at` (`expires_at`),
  KEY `idx_qr_tokens_used_at` (`used_at`),
  KEY `idx_qr_tokens_user_id` (`user_id`),
  CONSTRAINT `fk_qr_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `sessions` (
  `id` char(36) NOT NULL DEFAULT uuid(),
  `user_id` char(36) NOT NULL,
  `clock_in_time` datetime NOT NULL DEFAULT current_timestamp(),
  `clock_out_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `clock_in_log_id` char(36) DEFAULT NULL,
  `clock_out_log_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_clock_in_time` (`clock_in_time`),
  KEY `idx_sessions_clock_out_time` (`clock_out_time`),
  KEY `idx_sessions_user_status` (`user_id`,`status`),
  KEY `fk_sessions_clock_in_log` (`clock_in_log_id`),
  KEY `fk_sessions_clock_out_log` (`clock_out_log_id`),
  CONSTRAINT `fk_sessions_clock_in_log` FOREIGN KEY (`clock_in_log_id`) REFERENCES `attendance_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sessions_clock_out_log` FOREIGN KEY (`clock_out_log_id`) REFERENCES `attendance_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_sessions_clock_out_after_in` CHECK (`clock_out_time` is null or `clock_out_time` > `clock_in_time`),
  CONSTRAINT `chk_sessions_duration_positive` CHECK (`duration_minutes` is null or `duration_minutes` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `users` (
  `user_id` char(36) NOT NULL DEFAULT uuid(),
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `employee_id` varchar(50) NOT NULL DEFAULT '',
  `role` enum('staff','manager','admin') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `img` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_employee_id` (`employee_id`),
  UNIQUE KEY `uq_users_email` (`email`),
  CONSTRAINT `chk_users_email` CHECK (`email` regexp '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
