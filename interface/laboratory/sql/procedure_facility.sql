CREATE TABLE IF NOT EXISTS `procedure_facility` (
  `code` varchar(31) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `street2` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `npi` varchar(31) DEFAULT NULL,
  `clia` varchar(25) DEFAULT NULL,
  `lab_id` bigint(20) NOT NULL COMMENT 'procedure_provider.ppid',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
