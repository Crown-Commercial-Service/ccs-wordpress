CREATE TABLE `ccs_suppliers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `salesforce_id` varchar(20) DEFAULT NULL,
  `duns_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `postcode` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `trading_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;