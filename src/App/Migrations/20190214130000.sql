CREATE TABLE `ccs_lots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `framework_id` varchar(20) DEFAULT NULL,
  `wordpress_id` varchar(20) DEFAULT NULL,
  `salesforce_id` varchar(20) DEFAULT NULL,
  `lot_number` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `hide_suppliers` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `framework_id` (`framework_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
