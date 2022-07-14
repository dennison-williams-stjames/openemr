CREATE TABLE IF NOT EXISTS `procedure_order_code` (
  `procedure_order_id` bigint(20) NOT NULL COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11) NOT NULL COMMENT 'Supports multiple tests per order. Procedure_order_seq incremented in code',
  `procedure_code` varchar(31) NOT NULL DEFAULT '' COMMENT 'like procedure_type.procedure_code',
  `procedure_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'descriptive name of the procedure code',
  `procedure_source` char(1) NOT NULL DEFAULT '1' COMMENT '1=original order, 2=added after order sent',
  `diagnoses` text,
  `do_not_send` tinyint(1) DEFAULT '0' COMMENT '0 = normal, 1 = do not transmit to lab',
  `procedure_order_title` varchar(255) DEFAULT NULL,
  `pocedure_type` varchar(31) DEFAULT NULL,
  `transport` varchar(31) DEFAULT NULL,
  `reflex_code` varchar(31) DEFAULT NULL,
  `reflex_set` varchar(31) DEFAULT NULL,
  `reflex_name` varchar(31) DEFAULT NULL,
  `labcorp_zseg` varchar(31) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`procedure_order_id`,`procedure_order_seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
