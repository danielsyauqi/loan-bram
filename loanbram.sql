-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table loan_bram.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `address_line_1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `employment_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  KEY `addresses_employment_id_foreign` (`employment_id`),
  CONSTRAINT `addresses_employment_id_foreign` FOREIGN KEY (`employment_id`) REFERENCES `employments` (`id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.addresses: ~5 rows (approximately)
INSERT INTO `addresses` (`id`, `address_line_1`, `address_line_2`, `city`, `state`, `zip`, `country`, `user_id`, `employment_id`, `created_at`, `updated_at`) VALUES
	(1, '21, Jalan Sentosa 3', 'Taman Nusa Damai', 'Pasir Gudang', 'JOHOR', '81700', 'Malaysia', 1, NULL, '2025-03-10 23:19:30', '2025-03-10 23:19:30'),
	(2, '100, Jalan Tun Perak', 'Bukit Bintang', NULL, NULL, '50050', 'Malaysia', 2, NULL, '2025-03-12 21:00:14', '2025-04-09 06:00:53'),
	(4, '100, Jalan Tun Perak', 'Bukit Bintang', 'Kuala Lumpur', 'W.P. Kuala Lumpur', '50050', 'Malaysia', NULL, 1, '2025-03-19 05:19:38', '2025-03-25 20:30:35'),
	(5, '21, Jalan Sentosa 3, Taman Nusa Damai', NULL, 'Pasir Gudang', 'JOHOR', '81700', 'Malaysia', 4, NULL, '2025-03-26 23:50:11', '2025-03-26 23:50:11'),
	(9, 'Lingkaran Sunway Velocity, Sunway Velocity,', 'Wangsa Maju Seksyen 10', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', '81700', 'Malaysia', 8, NULL, '2025-04-09 21:50:08', '2025-04-09 21:50:08');

-- Dumping structure for table loan_bram.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.cache: ~4 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('loanbram_cache_22d200f8670dbdb3e253a90eee5098477c95c23d', 'i:2;', 1745844171),
	('loanbram_cache_22d200f8670dbdb3e253a90eee5098477c95c23d:timer', 'i:1745844171;', 1745844171),
	('loanbram_cache_7719a1c782a1ba91c031a682a0a2f8658209adbf', 'i:2;', 1745488654),
	('loanbram_cache_7719a1c782a1ba91c031a682a0a2f8658209adbf:timer', 'i:1745488654;', 1745488654);

-- Dumping structure for table loan_bram.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.cache_locks: ~0 rows (approximately)

-- Dumping structure for table loan_bram.employments
CREATE TABLE IF NOT EXISTS `employments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pension` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_joined` date DEFAULT NULL,
  `account_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emp_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_employments_users` (`user_id`) USING BTREE,
  CONSTRAINT `FK_employments_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.employments: ~1 rows (approximately)
INSERT INTO `employments` (`id`, `user_id`, `job_title`, `phone_num`, `bank`, `pension`, `company_name`, `date_joined`, `account_num`, `emp_type`) VALUES
	(1, 2, 'Frontend Developer', '01137781946', 'Maybank', '60', 'Maybank Berhad', '2025-03-19', '023422203', 'Full Time');

-- Dumping structure for table loan_bram.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table loan_bram.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.jobs: ~0 rows (approximately)

-- Dumping structure for table loan_bram.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.job_batches: ~0 rows (approximately)

-- Dumping structure for table loan_bram.loan_applications
CREATE TABLE IF NOT EXISTS `loan_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `biro` varchar(255) DEFAULT NULL,
  `banca` varchar(255) DEFAULT NULL,
  `rates` decimal(5,2) DEFAULT NULL,
  `document_checklist` json DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `date_approved` date DEFAULT NULL,
  `date_disbursed` date DEFAULT NULL,
  `date_rejected` date DEFAULT NULL,
  `tenure_applied` int DEFAULT NULL,
  `tenure_approved` int DEFAULT NULL,
  `amount_applied` decimal(20,2) DEFAULT NULL,
  `amount_approved` decimal(20,2) DEFAULT NULL,
  `amount_disbursed` decimal(20,2) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `module_id` bigint unsigned DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `sub_agent_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`),
  KEY `agent_id` (`agent_id`),
  KEY `module_id` (`module_id`),
  KEY `sub_agent_id` (`sub_agent_id`),
  CONSTRAINT `agent_id` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`),
  CONSTRAINT `customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `module_id` FOREIGN KEY (`module_id`) REFERENCES `loan_modules` (`id`),
  CONSTRAINT `product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sub_agent_id` FOREIGN KEY (`sub_agent_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table loan_bram.loan_applications: ~1 rows (approximately)
INSERT INTO `loan_applications` (`id`, `customer_id`, `product_id`, `agent_id`, `biro`, `banca`, `rates`, `document_checklist`, `date_received`, `date_approved`, `date_disbursed`, `date_rejected`, `tenure_applied`, `tenure_approved`, `amount_applied`, `amount_approved`, `amount_disbursed`, `status`, `created_at`, `updated_at`, `module_id`, `reference_id`, `sub_agent_id`) VALUES
	(24, 2, 4, 4, 'Yes', 'Yes', 2.11, '["Copy Payslip", "IC Copy", "HRMIS Report"]', '2025-04-19', '2025-04-10', NULL, '2025-04-23', 3, NULL, NULL, NULL, NULL, 'Processing', '2025-04-19 09:22:55', '2025-04-19 09:58:01', 1, 'PWK-429884', NULL);

-- Dumping structure for table loan_bram.loan_modules
CREATE TABLE IF NOT EXISTS `loan_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.loan_modules: ~2 rows (approximately)
INSERT INTO `loan_modules` (`id`, `name`, `logo`, `description`, `status`, `created_at`, `updated_at`, `slug`) VALUES
	(1, 'KFH', 'storage/images/loan-modules/cXbV5Eh7htVwyCnj66YqIuUuKpxaK3FnJxySyWDO.png', 'Kuwait Finance House', 'Active', '2025-03-11 03:43:05', '2025-03-26 22:26:21', 'kfh'),
	(6, 'KFH-COSHARE', 'storage/images/loan-modules/bfDM2eaHfNHIvBUqvEqIbHcEjUNWhbaQHgJN6nR9.png', 'KFH Coshare', 'Active', '2025-03-27 23:59:26', '2025-03-27 23:59:26', 'kfh-coshare');

-- Dumping structure for table loan_bram.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.migrations: ~20 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_03_04_082048_create_customers_table', 1),
	(5, '2025_03_04_082049_create_employments_table', 1),
	(6, '2025_03_04_082103_create_addresses_table', 1),
	(7, '2025_03_04_082135_create_agents_table', 1),
	(8, '2025_03_04_082308_create_admins_table', 1),
	(9, '2025_03_04_082317_create_loan_modules_table', 1),
	(10, '2025_03_04_082325_create_products_table', 1),
	(11, '2025_03_04_082427_create_loan_applications_table', 1),
	(12, '2025_03_04_082434_create_salaries_table', 1),
	(13, '2025_03_04_082445_create_salary_attachments_table', 1),
	(14, '2025_03_04_082456_create_workflow_remarks_table', 1),
	(15, '2025_03_04_082504_create_redemptions_table', 1),
	(16, '2025_03_11_050129_add_popularity_to_products_table', 2),
	(17, '2025_03_11_080811_add_module_permissions_to_users_table', 3),
	(18, '2023_08_30_100000_create_notifications_table', 4),
	(19, '2025_04_03_162315_create_notifications_table', 5),
	(20, '2025_04_24_164743_add_email_verified_at_to_users_table', 6);

-- Dumping structure for table loan_bram.notification
CREATE TABLE IF NOT EXISTS `notification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `message` text,
  `status` text,
  `sender_id` bigint unsigned DEFAULT NULL,
  `receiver_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `FK_notification_receiver_id` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_notification_sender_id` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table loan_bram.notification: ~24 rows (approximately)
INSERT INTO `notification` (`id`, `message`, `status`, `sender_id`, `receiver_id`, `created_at`, `updated_at`, `read_at`, `reference_id`) VALUES
	(28, 'New application created by customer using reference ID #SKE-132743', 'read', 2, 1, '2025-04-10 05:11:48', '2025-04-10 16:08:24', '2025-04-10 16:08:24', 'SKE-132743'),
	(75, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:32', '2025-04-19 17:33:32', NULL, 'PWK-429884'),
	(76, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:32', '2025-04-19 17:33:32', NULL, 'PWK-429884'),
	(77, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:32', '2025-04-19 17:33:32', NULL, 'PWK-429884'),
	(78, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:40', '2025-04-19 17:33:40', NULL, 'PWK-429884'),
	(79, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:47', '2025-04-19 17:33:47', NULL, 'PWK-429884'),
	(80, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:33:47', '2025-04-19 17:33:47', NULL, 'PWK-429884'),
	(83, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:34:51', '2025-04-19 17:34:51', NULL, 'PWK-429884'),
	(84, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:34:51', '2025-04-19 17:34:51', NULL, 'PWK-429884'),
	(85, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:34:53', '2025-04-19 17:34:53', NULL, 'PWK-429884'),
	(86, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:02', '2025-04-19 17:35:02', NULL, 'PWK-429884'),
	(88, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:31', '2025-04-19 17:35:31', NULL, 'PWK-429884'),
	(89, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:38', '2025-04-19 17:35:38', NULL, 'PWK-429884'),
	(90, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:38', '2025-04-19 17:35:38', NULL, 'PWK-429884'),
	(91, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:42', '2025-04-19 17:35:42', NULL, 'PWK-429884'),
	(92, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:35:46', '2025-04-19 17:35:46', NULL, 'PWK-429884'),
	(94, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:36:12', '2025-04-19 17:36:12', NULL, 'PWK-429884'),
	(95, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:36:24', '2025-04-19 17:36:24', NULL, 'PWK-429884'),
	(96, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:37:27', '2025-04-19 17:37:27', NULL, 'PWK-429884'),
	(97, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:37:27', '2025-04-19 17:37:27', NULL, 'PWK-429884'),
	(98, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:37:31', '2025-04-19 17:37:31', NULL, 'PWK-429884'),
	(100, 'You have been assigned as Master Agent for loan application #PWK-429884', 'unread', 1, NULL, '2025-04-19 17:37:42', '2025-04-19 17:37:42', NULL, 'PWK-429884'),
	(102, 'New application created by customer using reference ID #NWB-548162', 'unread', 2, 1, '2025-04-19 17:59:28', '2025-04-19 17:59:28', NULL, 'NWB-548162'),
	(103, 'Delete request sent by customer using reference ID #NWB-548162', 'unread', 2, 1, '2025-04-19 18:02:28', '2025-04-19 18:02:28', NULL, '25');

-- Dumping structure for table loan_bram.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.password_reset_tokens: ~3 rows (approximately)
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
	('danielcruzz04@gmail.com', '$2y$12$ry6TQf0ynYbHnzQ943OJ5.IDslq.GmhYlcQSaC8t6X3KsQyKOEZqG', '2025-04-24 03:50:44'),
	('gamingalchemist1@gmail.com', '$2y$12$7bmWO/Q0ZipHoWGEAuJIVelh6HM24SK3/HdA/C/iteMPVNIcUEf9e', '2025-04-24 06:08:28'),
	('stardevforyou@gmail.com', '$2y$12$72/25QLjLjJaIPx9TmoUyOJGKQCe7tBSnGfA1xvnCXqHhqBuRS7Qu', '2025-04-24 05:13:56');

-- Dumping structure for table loan_bram.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_loan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maximum_loan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenure` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `popularity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_module_id_foreign` (`module_id`),
  CONSTRAINT `products_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `loan_modules` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.products: ~3 rows (approximately)
INSERT INTO `products` (`id`, `module_id`, `name`, `minimum_loan`, `maximum_loan`, `rate`, `tenure`, `popularity`, `created_at`, `updated_at`, `slug`) VALUES
	(4, 1, 'KFH-MCCM', '23000', '60000', '[1.65,2.11]', '2', 0, '2025-03-11 19:54:32', '2025-03-26 22:05:42', 'kfh-mccm'),
	(5, 6, 'Coshare-JCL06	', '32000', '40000', '[2.11, 3.11]', '4', 0, '2025-04-10 03:28:09', '2025-04-10 03:28:14', 'coshare-jcl06'),
	(9, 1, 'KFH-COSHARE', '20000', '50000', '[2,2.5]', '3', 0, '2025-03-26 23:02:48', '2025-03-26 23:02:48', 'kfh-coshare');

-- Dumping structure for table loan_bram.redemptions
CREATE TABLE IF NOT EXISTS `redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `bank_coop` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `redemption_amount` decimal(20,2) DEFAULT NULL,
  `monthly_installment` decimal(20,2) DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_redemptions_users` (`customer_id`) USING BTREE,
  CONSTRAINT `FK_redemptions_users` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.redemptions: ~1 rows (approximately)
INSERT INTO `redemptions` (`id`, `customer_id`, `bank_coop`, `expiry_date`, `redemption_amount`, `monthly_installment`, `remark`, `action`, `created_at`, `updated_at`) VALUES
	(1, 2, 'RHB Bank', '2040-02-13', 300000.00, 293.45, 'Personal Loan', NULL, '2025-04-09 02:34:09', '2025-04-09 05:37:21');

-- Dumping structure for table loan_bram.salaries
CREATE TABLE IF NOT EXISTS `salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `month` int DEFAULT NULL,
  `year` int DEFAULT NULL,
  `income` json DEFAULT NULL,
  `deduction` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attachments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salaries_user_id_foreign` (`customer_id`) USING BTREE,
  CONSTRAINT `FK_salaries_users` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.salaries: ~1 rows (approximately)
INSERT INTO `salaries` (`id`, `customer_id`, `month`, `year`, `income`, `deduction`, `created_at`, `updated_at`, `attachments`) VALUES
	(1, 2, 2, 2025, '[{"label": "Freelance", "amount": 2000}, {"label": "Part Time", "amount": 3000}]', '[{"label": "License", "amount": 500}, {"label": "Telco", "amount": 200}]', '2025-03-19 05:38:54', '2025-04-09 06:00:53', 'salary_attachments/1742962725_2_57c96f04-39cc-4751-986d-fe5603d4e77e.pdf');

-- Dumping structure for table loan_bram.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.sessions: ~25 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('2Avnfkeekmyaow1KTdM5gvGZN0WcwLIJYXpziFWL', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWhoWHdsUlBCUjZGRmdUbjlsaXZpaGJudzN1bk00d0xGWENTTVpGaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xNzIuMjAuMTAuNC9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846743),
	('49rjM0LSAD9wEzcVcMyuZFMHFd0ysN7NtKtKSwd8', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQklTQVJQaFVVWjR1d0tyaFpobzBRWVRTb3BBYjZzUEVQaG5CaTBiOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTg6Imh0dHA6Ly8xNzIuMjAuMTAuNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846665),
	('4WGeVn5tPMvRBqZ6w31HJ0FciOHdmmkRcHflSQfz', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWmF5UVV6U0NtTVk1QkZLaTdZY2Q5OExSUVdZbHpKcUhEUGZuYlUyUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2FuLWJyYW0udGVzdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846128),
	('7QIXq0M87QeeGpOttR55pJISBFkNSdtBpZDIyEGS', NULL, '172.20.10.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiWXFFeUJJYlRuc2owTDF1Wk9scldXcEJxQXBZdzNTMERtbk1Pb1djWCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846754),
	('9gwnUgEySB4UZl408o4L3lfOSDmFRxvpkeltMRov', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiczFRd3pnTGVpR0VScFpMSUhmeDd5ekoyTHI4RVFDVUwxcUh0NzhpaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTk6Imh0dHBzOi8vMTcyLjIwLjEwLjQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1745846487),
	('ajpdI0PkKBZ0Yq7HKEboCsmxcCCUe9yXOyp4ypEi', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiR1plaVVSV2tNV2tGeUtheFVzOWc0ZDN4cEs1ZDZBbTFvR09VQzAxTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846722),
	('ay3AX2PlRqCsq05PbpBL2NXMnEFfcv4Ytzru1O9Y', NULL, '172.20.10.4', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRG83RFpiYzJhdTl1empPczdmVUlGSDdzOE1hT0pqZm1hYUlzODNncCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTg6Imh0dHA6Ly8xNzIuMjAuMTAuNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846631),
	('BfFdeFq3oj0mFGpcbgDGWNG9uitcQr0WeVXDmgDm', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiT0pZbVEzd0txS3VoVkd4SE4yOVI4M1N5djBTZ3BDNGZHNDZMSGlVSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846709),
	('bZdfE83sEEvKYeALICsJN1nLAAo1lZo4w3pOm0nh', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieUNtTzdrYmJUTU9RcFJ5QTZ3cm5ZVEtCSThEMDdIS0p2MGoxOFhiVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846727),
	('CRzknKoTAltMCvBahSnwHqYZ070eCzbIeV5T9Zij', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiakM4WUhTWmRKUWFaeHRsMnc0eTRSZXl4cHNLS3RHMnZnV1ZkQmFaciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745847510),
	('G6PsPyc7rGXWUttMMkptxmuVaVN1ypYrnUJrlsy6', NULL, '172.20.10.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieVBNbW1LcGZGTEx1QjFHSGRkMGR6WGVRZFpXcXo2cnprMW4yVFJtWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846757),
	('h95YfHhqXgot3PutOtuxIGcuTqjAjJDj8tXJsadm', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnVveThwSGxid2dQd1VDamxGU3BOUlNXdW5xVWpwUEc0azQ1ZjQxSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2FuLWJyYW0udGVzdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846076),
	('ji0bdGy8s7jclUnYd92CWZJqVRutVVRvocsG94JT', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiSlljaU9jSUxsUmtrWndaUFFaczN2VXpjaDJYenBSTTlTU1R6STNwbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846696),
	('JsDskMSFtsKc9WKz3uWKoTvs5gpkrhxtruLmDCww', NULL, '172.20.10.4', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVhIZWxDTHRJdVN6eXRCYlBoWFRYTTZTYTdybk1aVTBRTXhMRDhhbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTk6Imh0dHBzOi8vMTcyLjIwLjEwLjQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1745846624),
	('m41YOQ73QMDSNddIceYFbJ8fGgl8KsT8uw1VzgzI', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHBPSjIyaloyRDhKQUVZVWc4UGVrTk5KODhuZjZvZThMMzF0ZElBaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vbG9hbi1icmFtLnRlc3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1745846065),
	('M74NjKqzSIe45qCH4II1tSlZMRUETrwZ70QvS8cw', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTNCeXVBMDlueDhlTzN6VVVla1piSkRPVHY5ZDNQYkxWaEpWdXpyWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2FuLWJyYW0udGVzdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846147),
	('njXS35hsoq3EpubI2Idgt3a3MmGDNrK1TWGqqoQx', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWRkb0oxN21EODh2NE9CWmMwYm1oQ3daUlg3T3pwRU9Sc1oya01hMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2FuLWJyYW0udGVzdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846187),
	('nvVCKDA86AzlZtv4zFEC64YQVuAxZ83wef8ARHXy', NULL, '172.20.10.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUU5PTkVlbFpIREZkWEFwY05WRWNReE9MMXNubHk4OVZiS1ZlRHhBRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846751),
	('qzXKicZNqjQuUFa4NtbBZhsbUeEvb3WcIvwxrfB5', NULL, '172.20.10.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic1k3clBYM3FZOWhSd3JQMUhGYzB6bzNJU0VnOTdrVGFNZ01oVGg0TCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xNzIuMjAuMTAuNC9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846748),
	('sTvXxay12dshsTedKawTr5Lqnkwdd2Jhw6Ij0VEf', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiN1FYcHVMZzQxMm1nQkRDWVMyZEhHcnJwRUVNWUhLOUo3Y2c4cldQaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846695),
	('sxU0uhJGbDRA3S2spd332COaW5oKHFmrKuvRuvdS', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVGsyMG9tRnJ4c3BQTVZ0NjllTkk4ZWpOVGhvMDl2Rlc4bUtnRnFVWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846725),
	('u1pqEkqyKsqRpcY1dVSkcWzGAN2CDlnWcONCMP2E', NULL, '172.20.10.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoibTRIa2tkQUZUZm5YZmZ0ejlNSG9DcEt3Z25aMjdmSWJSUlF2eWFWOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1745846751),
	('ukW6mmatOVQ3M3DTReRZCoWBWKIGMPVmkPWuoPfK', NULL, '172.20.10.4', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXNwek5jbVI1Z3lsM3BCSnFpa0NBUG1LU3NuVG5FSFVyOEZMczczNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTg6Imh0dHA6Ly8xNzIuMjAuMTAuNCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846653),
	('VRC44m9lOP4O5SAJxwAaJUL2aSMaY6gpqO1fUmSB', 30, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaVNDSGZ2clozYTc4UVJsWjM0Y1F5bk1MdHBzYVpoVElqMnR1QTc4VCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jdXN0b21lci1kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozMDt9', 1745853239),
	('WbwwNmnAhKXl4yDFSfTi1V7Kf61lfDCncYs6NvtM', NULL, '172.20.10.12', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR2VNTUN6NEl4bXAySUJoQVhocUZZaDJ0ZGVFNVBGd0NFdG1ETEc3RCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xNzIuMjAuMTAuNC9yZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1745846674);

-- Dumping structure for table loan_bram.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ic_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_permissions` json DEFAULT NULL,
  `user_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.users: ~5 rows (approximately)
INSERT INTO `users` (`id`, `name`, `username`, `phone_num`, `bank_name`, `bank_account`, `role`, `status`, `ic_num`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_photo`, `module_permissions`, `user_code`) VALUES
	(1, 'Daniel Syauqi', 'admin', '01137781946', 'Maybank', '200323201', 'admin', 'active', '040914011141', 'stardevforyou@gmail.com', '2025-04-24 08:50:30', '$2y$12$wP/gMiKMowQoR386vorEDesCi.pEGZNzB8eNdrL4kMboxPsm426z6', 'ZAuZWc4VnRzs2yoXN4EeZHxXtsntItH9UHbeQt9ZQAjB53srPGwiHUBu4i0z', '2025-03-07 22:26:01', '2025-04-19 10:19:04', 'images/users/GwSwmftbC63Q511IGNTZPeEmZVWCzV3rDdfPtvSd.jpg', '[1, 6]', NULL),
	(2, 'Customer', 'customer', '01137781942', 'Maybank', '212311121', 'customer', 'active', '010101010101', 'customer@customer.com', '2025-04-24 08:50:31', '$2y$12$39KJrPKwTg0bpMJoq827j.mS.w.sd4Cbe08aBYuUaT71M3bYJtmrW', NULL, '2025-03-12 21:00:14', '2025-03-25 23:31:19', 'images/users/flpm6P9NMv0MNI8uRO95xTvqLpC2soTOW5DWDYjd.jpg', NULL, NULL),
	(4, 'Agent', 'agent', '019282283', NULL, NULL, 'agent', 'active', '020202020202', 'agent@agent.com', '2025-04-24 08:50:32', '$2y$12$//Ulo3REA2pGqohbEQHFyOlwcMQI9NAUlxUVhCfXQM3QLk0XagbrS', NULL, '2025-03-26 23:50:11', '2025-03-27 23:59:02', NULL, '[1]', NULL),
	(8, 'Sub Agent', 'subagent', '0122133321', NULL, NULL, 'sub agent', 'active', '012321122301', 'subagent@subagent.com', '2025-04-24 08:50:32', '$2y$12$eiVE6VgSaTGlCMAdYW8wAe.ppRMO12x3mYiu9/dvWNjRishInnbvq', NULL, '2025-04-09 21:50:08', '2025-04-09 21:50:08', NULL, '[1]', NULL),
	(30, 'test', 'test123', '60113778194', NULL, NULL, 'customer', 'not active', '020302010101', 'danielcruzz04@gmail.com', '2025-04-28 12:42:04', '$2y$12$yDrB1IqJfi5.x69A0A7/xObwCmp4/RWRLyaJYYsr981I1NdRZtpbG', NULL, '2025-04-28 12:38:46', '2025-04-28 12:42:04', NULL, NULL, NULL);

-- Dumping structure for table loan_bram.workflow_remarks
CREATE TABLE IF NOT EXISTS `workflow_remarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_remarks_application_id_foreign` (`application_id`),
  CONSTRAINT `workflow_remarks_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table loan_bram.workflow_remarks: ~3 rows (approximately)
INSERT INTO `workflow_remarks` (`id`, `application_id`, `remarks`, `created_at`, `updated_at`, `status`) VALUES
	(73, 24, '<p>PASS TO DATA ENTRY</p>', '2025-04-19 09:22:55', '2025-04-19 09:22:55', 'New'),
	(74, 24, '<p>Test</p>', '2025-04-19 09:27:08', '2025-04-19 09:27:08', 'Ready to Submit'),
	(75, 24, '<p>Processing</p><p><br></p>', '2025-04-19 09:27:54', '2025-04-19 09:27:54', 'Processing');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
