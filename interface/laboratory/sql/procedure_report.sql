CREATE TABLE IF NOT EXISTS `procedure_report` (
  `procedure_report_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `procedure_order_id` bigint(20) DEFAULT NULL COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11) DEFAULT '1' COMMENT 'references procedure_order_code.procedure_order_seq',
  `date_collected` datetime DEFAULT NULL,
  `date_collected_tz` varchar(5) DEFAULT NULL COMMENT '+-hhmm offset from UTC',
  `date_report` datetime DEFAULT NULL,
  `date_report_tz` varchar(5) DEFAULT NULL COMMENT '+-hhmm offset from UTC',
  `source` bigint(20) DEFAULT '0' COMMENT 'references users.id, who entered this data',
  `specimen_num` varchar(63) DEFAULT '',
  `report_status` varchar(31) DEFAULT '' COMMENT 'received,complete,error',
  `review_status` varchar(31) DEFAULT 'received' COMMENT 'pending review status: received,reviewed',
  `report_notes` text COMMENT 'notes from the lab',
  `lab_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`procedure_report_id`),
  KEY `procedure_order_id` (`procedure_order_id`),
  KEY `date_report_idx` (`date_report`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
