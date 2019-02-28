CREATE TABLE `ccs_lot_supplier` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lot_id` varchar(20) DEFAULT NULL,
  `supplier_id` varchar(20) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `website_contact` tinyint(1) NOT NULL DEFAULT '0',
  `trading_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;