CREATE TABLE IF NOT EXISTS `procedure_specimen` (
  `procedure_specimen_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `procedure_report_id` bigint(20) NOT NULL COMMENT 'references procedure_report.procedure_report_id',
  `specimen_number` varchar(255) DEFAULT '',
  `specimen_type` varchar(255) DEFAULT '',
  `type_modifier` varchar(255) DEFAULT '',
  `specimen_additive` varchar(255) DEFAULT NULL,
  `collection_method` varchar(255) DEFAULT '',
  `source_site` varchar(255) DEFAULT NULL,
  `source_quantifier` varchar(255) DEFAULT '',
  `specimen_volume` varchar(255) DEFAULT '',
  `specimen_condition` varchar(255) DEFAULT NULL,
  `specimen_rejected` varchar(255) DEFAULT NULL,
  `collected_datetime` datetime DEFAULT NULL,
  `received_datetime` datetime DEFAULT NULL,
  `detail_notes` text COMMENT 'OBX data content',
  PRIMARY KEY (`procedure_specimen_id`),
  KEY `procedure_report_id` (`procedure_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
