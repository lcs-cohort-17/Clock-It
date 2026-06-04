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